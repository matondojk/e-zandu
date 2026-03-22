<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CampaignResource\Pages;
use App\Models\Campaign;
use App\Models\Product;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Newsletter;
use App\Services\IntelligenceService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use App\Services\SubscriptionManager;

class CampaignResource extends Resource
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $modelLabel = 'Campanha';
    protected static ?string $pluralModelLabel = 'Campanhas';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $slug = 'campanhas';


    public static function getNavigationBadge(): ?string
    {
        $manager = app(SubscriptionManager::class);

        // A feature key é 'analise-inteligente', como definido no Middleware/Manager
        if (!$manager->canUse('campanhas')) {
            return 'PRO'; // Retorna o badge para o plano "Trial"
        }

        // Se pode usar (É Essencial ou PRO), não mostra nada
        return null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // --- PASSO 1: CONFIGURAÇÃO & CONTEÚDO ---
                    Wizard\Step::make('1. Configuração e Conteúdo')
                        ->schema([
                            Forms\Components\Section::make('Configuração da Campanha')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome da Campanha (Interno)')
                                        ->required(),

                                    // ✅ NOVO CAMPO DE STATUS
                                    Forms\Components\Select::make('status')
                                        ->label('Ação da Campanha')
                                        ->options([
                                            'draft' => 'Salvar como Rascunho',
                                            'processing' => 'Enviar Agora (Imediato)',
                                            'scheduled' => 'Agendar Envio',
                                        ])
                                        ->default('draft')
                                        ->live()
                                        ->required()
                                        ->helperText('Se escolher "Enviar Agora", o envio começa assim que salvar.'),

                                    Forms\Components\Select::make('channel')
                                        ->label('Canal de Envio')
                                        ->options([
                                            'whatsapp' => 'Apenas WhatsApp',
                                            'email' => 'Apenas Email',
                                            'both' => 'WhatsApp e Email (Ambos)',
                                        ])
                                        ->required()
                                        ->live()
                                        ->helperText('O envio respeita se o cliente tem email/telefone preenchido.'),

                                    // ✅ VISIBILIDADE CONDICIONAL CORRIGIDA
                                    Forms\Components\DateTimePicker::make('scheduled_at')
                                        ->label('Data e Hora do Agendamento')
                                        ->visible(fn(Get $get) => $get('status') === 'scheduled')
                                        ->requiredIf('status', 'scheduled')
                                        ->native(false),
                                    // ->minDate(now()),

                                    Forms\Components\Radio::make('content_type')
                                        ->label('Tipo de Conteúdo')
                                        ->options([
                                            'message' => 'Mensagem Simples',
                                            'coupon' => 'Anexar Cupão',
                                            'product_promo' => 'Promover Produtos',
                                        ])
                                        ->default('message')
                                        ->reactive()
                                        ->columnSpanFull(),
                                ]),

                            Forms\Components\Section::make('Detalhes da Promoção')
                                ->visible(fn(Get $get) => in_array($get('content_type'), ['coupon', 'product_promo']))
                                ->schema([
                                    Forms\Components\Select::make('coupon_id')
                                        ->label('Selecionar Cupão')
                                        ->relationship('coupon', 'code', fn(Builder $query) => $query->where('is_active', true))
                                        ->searchable()
                                        ->visible(fn(Get $get) => $get('content_type') === 'coupon')
                                        ->requiredIf('content_type', 'coupon')
                                        ->reactive(),

                                    Forms\Components\Repeater::make('productsInPromotion')
                                        ->label('Produtos em Promoção')
                                        ->schema([
                                            Forms\Components\Select::make('product_id')
                                                ->label('Produto')
                                                ->searchable()
                                                ->getSearchResultsUsing(fn(string $search) => Product::where('name', 'like', "%{$search}%")->limit(50)->pluck('name', 'id'))
                                                ->getOptionLabelUsing(fn($value): ?string => Product::find($value)?->name)
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (Set $set, $state) {
                                                    $product = Product::find($state);
                                                    $set('original_price', $product?->price);
                                                    $price = $product?->promotional_price > 0 ? $product->promotional_price : $product?->price;
                                                    $set('promotional_price', $price);
                                                }),
                                            Forms\Components\TextInput::make('original_price')
                                                ->label('Preço Original (Kz)')
                                                ->disabled()
                                                ->numeric(),
                                            Forms\Components\TextInput::make('promotional_price')
                                                ->label('Preço Promocional (Kz)')
                                                ->numeric()
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->visible(fn(Get $get) => $get('content_type') === 'product_promo')
                                        ->requiredIf('content_type', 'product_promo'),
                                ]),
                        ]),

                    // --- PASSO 2: MENSAGEM & AUDIÊNCIA ---
                    Wizard\Step::make('2. Mensagem e Audiência')
                        ->schema([
                            Forms\Components\Section::make('Geração de Conteúdo e Templates')
                                ->description('Use a IA para gerar textos ou preencha manualmente.')
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

                                                $productsData = [];
                                                if ($get('content_type') === 'product_promo' && !empty($get('productsInPromotion'))) {
                                                    foreach ($get('productsInPromotion') as $promoItem) {
                                                        $prod = Product::find($promoItem['product_id']);
                                                        if ($prod) {
                                                            $productsData[] = [
                                                                'name' => $prod->name,
                                                                'slug' => $prod->slug,
                                                                'price_original' => $prod->price,
                                                                'promotional_price' => $promoItem['promotional_price']
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
                                        ->visible(fn(Get $get) => in_array($get('channel'), ['whatsapp', 'both']))
                                        ->required(fn(Get $get) => in_array($get('channel'), ['whatsapp', 'both'])),
                                    Forms\Components\TextInput::make('email_subject')
                                        ->label('Assunto do Email')
                                        ->visible(fn(Get $get) => in_array($get('channel'), ['email', 'both']))
                                        ->required(fn(Get $get) => in_array($get('channel'), ['email', 'both'])),
                                    Forms\Components\RichEditor::make('email_template')
                                        ->label('Corpo do Email')
                                        ->visible(fn(Get $get) => in_array($get('channel'), ['email', 'both']))
                                        ->required(fn(Get $get) => in_array($get('channel'), ['email', 'both'])),
                                ]),


                            Forms\Components\Section::make('Definição de Audiência')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Radio::make('audience_type')
                                        ->label('Selecionar Audiência')
                                        ->options(fn(Get $get) => array_filter([
                                            'all' => 'Todos os Clientes (com role "customer")',
                                            'manual' => 'Selecionar Manualmente',
                                            'ai_prompt' => 'Filtro por IA (Prompt)',
                                            ($get('channel') === 'email' || $get('channel') === 'both') ? 'newsletter' : null => 'Assinantes de Newsletter',
                                        ]))
                                        ->default('all')
                                        ->reactive(),

                                    Forms\Components\Select::make('manual_customers')
                                        ->label('Clientes Selecionados')
                                        ->multiple()
                                        ->searchable()
                                        ->getSearchResultsUsing(fn(string $search) => User::where('role', 'customer')->where(fn($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))->limit(50)->pluck('name', 'id'))
                                        ->getOptionLabelsUsing(fn(array $values): array => User::whereIn('id', $values)->pluck('name', 'id')->all())
                                        ->visible(fn(Get $get) => $get('audience_type') === 'manual')
                                        ->helperText('(Ignorado se não for seleção manual)')
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('audience_prompt')
                                        ->label('Prompt para filtrar clientes (IA)')
                                        ->placeholder("Ex: clientes que gastaram mais de 50000 Kz no total...")
                                        ->visible(fn(Get $get) => $get('audience_type') === 'ai_prompt')
                                        ->requiredIf('audience_type', 'ai_prompt')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Campanha')->searchable(),
                Tables\Columns\TextColumn::make('channel')
                    ->label('Canal')
                    ->badge() // Adiciona estilo de badge
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'whatsapp' => 'WhatsApp',
                        'email'    => 'E-mail',
                        'both'     => 'WhatsApp e E-mail',
                        default    => $state,
                    })
                    ->colors([
                        'success' => 'whatsapp',
                        'info'    => 'email',
                        'primary' => 'both',
                    ]),
                Tables\Columns\TextColumn::make('coupon.code')->label('Cupão')->placeholder('N/A'),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'draft' => 'Rascunho',
                        'scheduled' => 'Agendada',
                        'processing' => 'Processando',
                        'sent' => 'Enviada',
                        'failed' => 'Falhou',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'draft',
                        'info' => 'scheduled',
                        'warning' => 'processing',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ]),
                Tables\Columns\TextColumn::make('audience_count')
                    ->label('Público')
                    ->getStateUsing(function (Campaign $record) {
                        if ($record->audience_type === 'newsletter') {
                            return Newsletter::where('is_subscribed', true)->count() . ' (Newsletter)';
                        }
                        return $record->audience()->count();
                    })
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Agendada Para')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Imediato'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->visible(fn(Campaign $record) => in_array($record->status, ['draft', 'scheduled', 'failed'])),
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->visible(fn(Campaign $record) => $record->status !== 'processing'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCampaigns::route('/'),
            'create' => Pages\CreateCampaign::route('/create'),
            'view' => Pages\ViewCampaign::route('/{record}/view'),
            'edit' => Pages\EditCampaign::route('/{record}/edit'),
        ];
    }
}
