<?php

namespace App\Filament\Pages;

use App\Models\SubscriptionDetail;
use App\Models\SubscriptionHistory;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\HtmlString;

class ManageSubscription extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Minha Assinatura';
    protected static ?string $title = 'Gerenciamento de Assinatura';

    // Configurações de Navegação Solicitadas
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?string $slug = 'assinaturas';

    protected static string $view = 'filament.pages.manage-subscription';

    public ?SubscriptionDetail $subscription = null;

    public function mount()
    {
        $this->subscription = SubscriptionDetail::first();
    }

    // Prepara dados para a View (incluindo a lógica dos alertas)
    protected function getViewData(): array
    {
        $alertType = null; // null, 'warning', 'danger'
        $alertMessage = null;

        if ($this->subscription && $this->subscription->ends_at) {
            $endsAt = Carbon::parse($this->subscription->ends_at);
            $now = Carbon::now();
            $daysRemaining = $now->diffInDays($endsAt, false); // false para permitir negativos

            // Cenário 1: Expirado
            if ($daysRemaining < 0) {
                $alertType = 'danger';
                $alertMessage = "A sua assinatura expirou! Para continuar a gerenciar a sua loja sem interrupções, por favor faça a renovação agora.";
            }
            // Cenário 2: Expira em 7 dias ou menos
            elseif ($daysRemaining <= 7) {
                $alertType = 'warning';
                $daysText = $daysRemaining == 0 ? 'hoje' : "em {$daysRemaining} dias";
                $alertMessage = "Atenção: A sua assinatura expira {$daysText}. Faça a renovação antecipada para evitar bloqueios.";
            }
        }

        return [
            'subscription' => $this->subscription,
            'alertType' => $alertType,
            'alertMessage' => $alertMessage,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SubscriptionHistory::query()->latest())
            ->heading('Histórico de Pagamentos')
            ->columns([
                TextColumn::make('created_at')->dateTime('d/m/Y H:i')->label('Data'),
                TextColumn::make('event')
                    ->label('Evento')
                    ->badge()
                    ->colors([
                        'success' => ['subscription.activated', 'subscription.renewed'],
                        'danger' => ['subscription.canceled'],
                        'warning' => ['subscription.paused'],
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'subscription.activated' => 'Ativação',
                        'subscription.renewed' => 'Renovação',
                        'subscription.paused' => 'Pausa',
                        'subscription.canceled' => 'Cancelamento',
                        default => $state,
                    }),
                TextColumn::make('description')
                    ->label('Descrição')
                    ->wrap(),

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('AOA') 

            ])
            ->paginated([5, 10]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('renovar')
                ->label('Renovar / Alterar Plano')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->modalHeading('Renovação / Alteração de Plano')
                ->modalWidth('2xl')
                ->form(function () {
                    $baseUrl = config('services.central_api_url', 'https://manage.matondojoao.com');
                    $apiToken = env('API_SHARED_TOKEN');

                    try {
                        $plans = Http::withHeaders(['X-API-TOKEN' => $apiToken])
                            ->get("$baseUrl/api/public/plans", [
                                'current_plan_identifier' => $this->subscription?->plan_identifier
                            ])
                            ->json();

                        $methods = Http::withHeaders(['X-API-TOKEN' => $apiToken])
                            ->get("$baseUrl/api/public/payment-methods")
                            ->json();
                    } catch (\Exception $e) {
                        return [Placeholder::make('error')->content('Erro de conexão. Verifique o servidor.')];
                    }

                    if (!$plans || !$methods) {
                        return [Placeholder::make('error')->content('Serviço indisponível ou Token inválido.')];
                    }

                    $plansOptions = collect($plans)->pluck('name', 'id');
                    $plansPrices = collect($plans)->pluck('price', 'id');
                    $methodsOptions = collect($methods)->pluck('title', 'id');
                    $methodsData = collect($methods)->keyBy('id');

                    // LÓGICA DE SELEÇÃO PADRÃO (Define o plano atual)
                    $currentPlanId = null;
                    if ($this->subscription?->plan_identifier) {
                        $found = collect($plans)->firstWhere('identifier', $this->subscription->plan_identifier);
                        $currentPlanId = $found['id'] ?? null;
                    }

                    return [
                        Grid::make(2)->schema([
                            Select::make('new_plan_id')
                                ->label('Plano')
                                ->options($plansOptions)
                                ->default($currentPlanId)
                                ->required()
                                ->live()
                                ->afterStateUpdated(
                                    fn(Set $set, $state) =>
                                    $set('price_display', number_format($plansPrices[$state] ?? 0, 2) . ' Kz')
                                ),

                            // Exibe o preço do plano selecionado
                            Placeholder::make('price_display')
                                ->label('Valor a Pagar')
                                ->content(
                                    fn(Get $get) =>
                                    number_format($plansPrices[$get('new_plan_id')] ?? ($plansPrices[$currentPlanId] ?? 0), 2) . ' Kz'
                                )
                                ->extraAttributes(['class' => 'font-bold text-xl text-primary-600']),
                        ]),

                        // Feedback visual se é renovação ou alteração
                        Placeholder::make('action_info')
                            ->hiddenLabel()
                            ->content(function (Get $get) use ($currentPlanId) {
                                if (!$currentPlanId) return '';
                                $selected = $get('new_plan_id');
                                if ($selected && $selected !== $currentPlanId) {
                                    return new HtmlString('<span class="text-sm text-yellow-600 font-medium">Você selecionou um plano diferente. Isso será processado como uma alteração de plano.</span>');
                                }
                                return new HtmlString('<span class="text-sm text-gray-500">Mantendo o plano atual (Renovação padrão).</span>');
                            }),

                        Select::make('payment_method_id')
                            ->label('Método de Pagamento')
                            ->options($methodsOptions)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) use ($methodsData) {
                                $method = $methodsData[$state] ?? null;
                                if ($method) {
                                    $desc = $method['type'] === 'bank_transfer'
                                        ? "Banco: {$method['bank_name']} | IBAN: {$method['iban']}"
                                        : "Telefone: {$method['phone_number']}";
                                    $set('instructions', $desc);
                                }
                            }),

                        Placeholder::make('instructions_view')
                            ->label('Dados para Pagamento')
                            ->content(fn(Get $get) => $get('instructions') ?? '-'),

                        FileUpload::make('proof_file')
                            ->label('Comprovativo')
                            ->helperText('Formatos: PDF, JPG, PNG. Max: 5MB')
                            ->disk('public')
                            ->directory('temp_proofs')
                            ->visibility('public')
                            ->required(),
                    ];
                })
                ->action(function (array $data) {
                    $baseUrl = config('services.central_api_url', 'https://manage.matondojoao.com');
                    $apiToken = env('API_SHARED_TOKEN');

                    $relativePath = $data['proof_file'];
                    $fullPath = public_path($relativePath);

                    if (!file_exists($fullPath)) {
                        $fullPath = public_path(ltrim($relativePath, '/'));
                        if (!file_exists($fullPath)) {
                            Log::error("Arquivo não encontrado em: {$fullPath}");
                            Notification::make()->title("Erro interno: Arquivo temporário não encontrado.")->danger()->send();
                            return;
                        }
                    }

                    try {
                        $response = Http::withHeaders(['X-API-TOKEN' => $apiToken])
                            ->attach(
                                'proof_file',
                                file_get_contents($fullPath),
                                basename($fullPath)
                            )
                            ->post("$baseUrl/api/public/upload-proof", [
                                'subscription_id' => $this->subscription->central_subscription_id,
                                'payment_method_id' => $data['payment_method_id'],
                                'new_plan_id' => $data['new_plan_id'],
                            ]);

                        if ($response->successful()) {
                            Notification::make()->title('Comprovativo enviado! Aguarde a aprovação.')->success()->send();
                            @unlink($fullPath);
                        } else {
                            Log::error('Erro API Manage: ' . $response->body());
                            Notification::make()->title('Erro ao enviar. Tente novamente.')->danger()->send();
                        }
                    } catch (\Exception $e) {
                        Log::error('Exceção Upload: ' . $e->getMessage());
                        Notification::make()->title('Erro de conexão ao enviar.')->danger()->send();
                    }
                })
        ];
    }
}
