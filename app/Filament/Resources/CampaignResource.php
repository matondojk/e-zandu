<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\User; // ✅ Usar USER
use App\Models\Coupon;
use App\Services\IntelligenceService; // ✅ Importar IA
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification; // Para feedback
use App\Services\SubscriptionManager;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $modelLabel = 'Campanha';
    protected static ?string $pluralModelLabel = 'Campanhas';


    public static function getNavigationBadge(): ?string
    {
        $manager = app(SubscriptionManager::class);

        // Usamos 'whatsapp' como a flag principal para "PRO"
        // (Baseado no middleware CheckTenantSubscription)
        if (!$manager->canUse('whatsapp')) {
            return 'PRO';
        }
        return null;
    }

    /**
     * Define a cor do badge.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        if (!app(SubscriptionManager::class)->canUse('whatsapp')) {
            return 'warning';
        }
        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // --- PASSO 1: O QUÊ? ---
                    Wizard\Step::make('Campanha')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->label('Nome da Campanha (Interno)')
                                ->required(),
                            Forms\Components\Select::make('channel')
                                ->label('Canal de Envio')
                                ->options([
                                    'whatsapp' => 'Apenas WhatsApp',
                                    'email' => 'Apenas Email',
                                    'both' => 'WhatsApp e Email (Ambos)',
                                ])
                                ->required()
                                ->helperText('O envio respeita se o cliente tem email/telefone preenchido.'),
                            Forms\Components\DateTimePicker::make('scheduled_at')
                                ->label('Agendar Envio Para')
                                ->helperText('Deixe em branco para enviar assim que for aprovada.'),
                        ]),

                    // --- PASSO 2: CONTEÚDO ---
                    Wizard\Step::make('Conteúdo')
                        ->schema([
                            Forms\Components\Radio::make('content_type')
                                ->label('Tipo de Conteúdo')
                                ->options([
                                    'message' => 'Mensagem Simples',
                                    'coupon' => 'Anexar Cupão',
                                    'product_promo' => 'Promover Produtos',
                                ])
                                ->default('message')
                                ->reactive(),

                            Forms\Components\Select::make('coupon_id')
                                ->label('Selecionar Cupão')
                                ->relationship('coupon', 'code', fn(Builder $query) => $query->where('is_active', true))
                                ->searchable()
                                ->visible(fn(Get $get) => $get('content_type') === 'coupon')
                                ->requiredIf('content_type', 'coupon')
                                ->reactive(),

                            // Seletor de Produtos para Promoção
                            Forms\Components\Repeater::make('productsInPromotion')
                                ->label('Produtos em Promoção')
                                ->relationship('productsInPromotion') // Usa a Relação
                                ->schema([
                                    // 👇 GRANDE CORREÇÃO AQUI (Carregamento Sob Demanda) 👇
                                    Forms\Components\Select::make('product_id')
                                        ->label('Produto')
                                        ->searchable()
                                        // Não use ->options(), use ->getSearchResultsUsing() para performance
                                        ->getSearchResultsUsing(fn(string $search) => Product::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'))
                                        ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->name)
                                        // 👆 FIM DA CORREÇÃO 👆
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            $product = Product::find($state);
                                            // Puxa o preço promocional se existir, senão o preço normal
                                            $price = $product?->promotional_price > 0 ? $product->promotional_price : $product?->price;
                                            $set('promotional_price', $price);
                                        }),
                                    Forms\Components\TextInput::make('promotional_price')
                                        ->label('Preço Promocional (Kz)')
                                        ->numeric()
                                        ->required(),
                                ])
                                ->columns(2)
                                ->visible(fn(Get $get) => $get('content_type') === 'product_promo')
                                ->requiredIf('content_type', 'product_promo'),
                        ]),

                    // --- PASSO 3: MENSAGEM (IA) ---
                    Wizard\Step::make('Mensagem')
                        ->schema([
                            Forms\Components\Textarea::make('ai_content_prompt')
                                ->label('Prompt para gerar conteúdo (IA)')
                                ->placeholder('Ex: Tom amigável, falar sobre a promo de tênis, usar gírias de Angola, incluir o cupão')
                                ->rows(3),

                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('generate_content')
                                    ->label('Gerar Textos com IA')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function (Get $get, Set $set, IntelligenceService $ai) {
                                        $prompt = $get('ai_content_prompt');
                                        if (empty($prompt)) {
                                            Notification::make()->title('Prompt Vazio')->body('Escreva o que deseja no prompt.')->warning()->send();
                                            return;
                                        }
                                        $coupon = Coupon::find($get('coupon_id'));

                                        // Coletar dados dos produtos
                                        $productsData = [];
                                        if ($get('content_type') === 'product_promo' && !empty($get('productsInPromotion'))) {
                                            foreach ($get('productsInPromotion') as $promoItem) {
                                                $prod = Product::find($promoItem['product_id']);
                                                if ($prod) {
                                                    $productsData[] = [
                                                        'name' => $prod->name,
                                                        'price' => $promoItem['promotional_price']
                                                    ];
                                                }
                                            }
                                        }

                                        $response = $ai->generateCampaignTemplates(
                                            $prompt,
                                            $get('content_type'),
                                            $productsData,
                                            $coupon?->code
                                        );

                                        $set('whatsapp_template', $response['whatsapp_template']);
                                        $set('email_subject', $response['email_subject']);
                                        $set('email_template', $response['email_template']);

                                        Notification::make()->title('Textos Gerados!')->success()->send();
                                    }),
                            ]),

                            Forms\Components\Textarea::make('whatsapp_template')
                                ->label('Texto para WhatsApp')
                                ->helperText('Use {nomeCliente} e {cupao}')
                                ->rows(5)
                                ->required(),
                            Forms\Components\TextInput::make('email_subject')
                                ->label('Assunto do Email'),
                            Forms\Components\RichEditor::make('email_template')
                                ->label('Corpo do Email'),
                        ]),

                    // --- PASSO 4: AUDIÊNCIA (IA) ---
                    Wizard\Step::make('Audiência')
                        ->schema([
                            Forms\Components\Radio::make('audience_type')
                                ->label('Selecionar Audiência')
                                ->options([
                                    'all' => 'Todos os Clientes (com role "customer")',
                                    'manual' => 'Selecionar Manualmente',
                                    'ai_prompt' => 'Filtro por IA (Prompt)',
                                ])
                                ->default('all')
                                ->reactive(),

                            // 👇 GRANDE CORREÇÃO AQUI (Carregamento Sob Demanda) 👇
                            Forms\Components\Select::make('manual_customers')
                                ->label('Clientes Selecionados')
                                ->multiple()
                                ->searchable()
                                // Não use ->options(), use ->getSearchResultsUsing() para performance
                                ->getSearchResultsUsing(fn(string $search) => User::where('role', 'customer')->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))->limit(50)->pluck('name', 'id'))
                                ->getOptionLabelsUsing(fn(array $values): array => User::whereIn('id', $values)->pluck('name', 'id')->all())
                                // 👆 FIM DA CORREÇÃO 👆
                                ->visible(fn(Get $get) => $get('audience_type') === 'manual')
                                ->helperText('(Ignorado se não for seleção manual)'),

                            Forms\Components\Textarea::make('audience_prompt')
                                ->label('Prompt para filtrar clientes (IA)')
                                ->placeholder("Ex: clientes que gastaram mais de 50000 Kz no total...")
                                ->visible(fn(Get $get) => $get('audience_type') === 'ai_prompt')
                                ->requiredIf('audience_type', 'ai_prompt'),
                        ]),

                ])->columnSpanFull(), // Fim do Wizard
            ]); // Fim do Schema
    } // Fim do Form

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Campanha')->searchable(),
                Tables\Columns\TextColumn::make('channel')->label('Canal'),
                Tables\Columns\TextColumn::make('coupon.code')->label('Cupão')->placeholder('N/A'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'scheduled',
                        'warning' => 'processing',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('audience_count')
                    ->label('Público')
                    ->getStateUsing(fn(Campaign $record) => $record->audience()->count())
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('scheduled_at')->label('Agendada Para')->dateTime('d/m/Y H:i')->placeholder('Imediato'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn(Campaign $record) => $record->status === 'draft' || $record->status === 'scheduled'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // (Podemos adicionar um RelationManager para ver o status do público mais tarde)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            // Esta linha está correta e descomentada
            'view' => Pages\ViewCampaign::route('/{record}/view'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
