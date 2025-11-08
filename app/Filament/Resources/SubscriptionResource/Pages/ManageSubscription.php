<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Resources\SubscriptionResource;
use Filament\Resources\Pages\Page;
use App\Models\SubscriptionDetail;
use App\Models\SubscriptionHistory;
use Illuminate\Support\Facades\Http;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class ManageSubscription extends Page
{
    protected static string $resource = SubscriptionResource::class;
    protected static string $view = 'filament.resources.subscription-resource.pages.manage-subscription';
    protected static ?string $title = 'Assinatura e Pagamentos';

    // Propriedades para guardar os dados
    public ?SubscriptionDetail $details = null;
    public $history = null;
    public array $paymentMethods = [];
    public array $paymentMethodsOptions = [];

    // O mount() é o "construtor" da página
    public function mount(): void
    {
        $this->details = SubscriptionDetail::first();
        $this->history = SubscriptionHistory::latest()->get();
        
        // Tenta buscar os métodos de pagamento da API do SGS
        try {
            $response = Http::withToken(env('GESTOR_CENTRAL_API_TOKEN'))
                ->baseUrl(env('GESTOR_CENTRAL_API_URL'))
                ->get('v1/payment-methods');

            if ($response->successful()) {
                $this->paymentMethods = $response->json('data', []);
                
                // Prepara as opções para o Select
                foreach ($this->paymentMethods as $method) {
                    $this->paymentMethodsOptions[$method['id']] = $method['name'];
                }
            }
        } catch (\Exception $e) {
            Log::error("Falha ao buscar métodos de pagamento: " . $e->getMessage());
        }
    }

    // Ação para enviar o comprovativo
    protected function getHeaderActions(): array
    {
        return [
            Action::make('enviarComprovativo')
                ->label('Enviar Comprovativo')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                // Só mostra o botão se a assinatura estiver pendente
                ->visible(fn () => $this->details?->status === 'pending')
                ->form([
                    Select::make('payment_method_id')
                        ->label('Forma de Pagamento')
                        ->options($this->paymentMethodsOptions)
                        ->required(),
                    FileUpload::make('comprovativo')
                        ->label('Comprovativo (PDF, JPG, PNG)')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120)
                        ->disk('local') // Upload temporário
                ])
                ->action(function (array $data) {
                    $this->submitProof($data);
                }),
        ];
    }

    public function submitProof(array $data): void
    {
        try {
            $response = Http::withToken(env('GESTOR_CENTRAL_API_TOKEN'))
                ->baseUrl(env('GESTOR_CENTRAL_API_URL'))
                ->attach(
                    'comprovativo', // Nome do campo no backend (API)
                    fopen(storage_path('app/' . $data['comprovativo']), 'r'),
                    basename($data['comprovativo'])
                )
                ->post('v1/payment-proofs', [
                    'subscription_id' => $this->details->central_subscription_id,
                    'payment_method_id' => $data['payment_method_id'],
                ]);

            if ($response->successful()) {
                Notification::make()
                    ->title('Comprovativo enviado!')
                    ->body('O seu comprovativo foi enviado. Aguarde a aprovação do administrador.')
                    ->success()
                    ->send();
            } else {
                 Notification::make()->title('Erro ao enviar')->danger()->send();
            }
        } catch (\Exception $e) {
            Log::error("Falha ao enviar comprovativo: " . $e->getMessage());
            Notification::make()->title('Erro de Conexão')->danger()->send();
        }
    }
}