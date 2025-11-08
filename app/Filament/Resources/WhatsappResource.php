<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappResource\Pages;
use App\Models\WhatsAppSetting; // Usamos o modelo antigo (WhatsAppSetting)
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\SubscriptionManager; // 1. IMPORTAR O SERVIÇO DE ASSINATURA

class WhatsappResource extends Resource
{
    // Usamos o modelo WhatsAppSetting, mas o Resource chama-se Whatsapp
    protected static ?string $model = WhatsAppSetting::class; 

    // ✅ NOVO NOME, SLUG E GRUPO DE NAVEGAÇÃO
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'WhatsApp';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $modelLabel = 'WhatsApp';
    protected static ?string $pluralModelLabel = 'WhatsApp';
    
    // ✅ FORÇA O SLUG PARA EVITAR ERROS DE ROTA
    protected static ?string $slug = 'whatsapp';

    public static function getNavigationBadge(): ?string
    {
        // Resolve o serviço de subscrição
        $manager = app(SubscriptionManager::class);

        // Se o utilizador NÃO PODE usar 'whatsapp' (porque é Essencial), mostra o badge
        if (!$manager->canUse('whatsapp')) {
            return 'PRO';
        }

        // Se pode usar (é PRO), não mostra nada
        return null;
    }

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
                Forms\Components\Section::make('Credenciais da API')
                    ->description('Tokens e IDs fornecidos pela Meta.')
                    ->schema([
                        Forms\Components\TextInput::make('token')
                            ->label('Token da API')
                            ->password()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone_id')
                            ->label('ID do Número de Telefone')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('verify_token')
                            ->label('Token de Verificação (Webhook)')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Configurações Internas')
                    ->schema([
                        Forms\Components\TextInput::make('admin_phone')
                            ->label('Telefone do Administrador')
                            ->tel()
                            ->helperText('Número com código do país (ex: 2449xxxxxxx) para receber alertas.')
                            ->required()
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Tabela oculta, mas obrigatória
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('phone_id')->label('ID do Telefone'),
                Tables\Columns\TextColumn::make('admin_phone')->label('Admin'),
                Tables\Columns\TextColumn::make('updated_at')->label('Última Modificação')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }
    
    public static function getPages(): array
    {
        return [
            // O 'index' aponta para a nossa página "porteiro"
            'index' => Pages\ListWhatsapps::route('/'), 
            'create' => Pages\CreateWhatsapp::route('/create'),
            'edit' => Pages\EditWhatsapp::route('/{record}/edit'),
        ];
    }

    // Desativa o botão "Criar" globalmente
    public static function canCreate(): bool
    {
        return true;
    }
}