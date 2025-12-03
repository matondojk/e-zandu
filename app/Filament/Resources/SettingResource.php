<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ColorPicker; // <-- ADICIONADO: Importar ColorPicker

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Personalização';
    protected static ?string $modelLabel = 'Configuração';
    protected static ?string $pluralModelLabel = 'Configurações';
    protected static ?int $navigationSort = 100;
    protected static ?string $slug = 'personalizacao';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ----------------------------------------------------
                // SEÇÃO 1 — GERAL E BRANDING
                // ----------------------------------------------------
                Section::make('Geral e Branding')
                    ->description('Identidade da loja, cores e informações fiscais.')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsible()
                    ->schema([

                        TextInput::make('store_name')
                            ->label('Nome da Loja')
                            ->required(),

                        // NOVO: Campo para a Cor Principal
                        ColorPicker::make('primary_color_hex')
                            ->label('Cor Principal do Painel (Primary)')
                            ->helperText('Código hexadecimal da cor principal do Admin Panel.')
                            ->required()
                            ->default('#0284c7'), // Sky 600 como padrão

                        TextInput::make('nif')
                            ->label('NIF')
                            ->placeholder('Ex: 5000000000')
                            ->maxLength(50)
                            ->required(),

                        FileUpload::make('logo_light_path')
                            ->label('Logo Claro (Modo Dia)')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024),

                        FileUpload::make('logo_dark_path')
                            ->label('Logo Escuro (Modo Noite)')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024),

                        FileUpload::make('icon_path')
                            ->label('Favicon (.png)')
                            ->disk('public')
                            ->directory('branding')
                            ->visibility('public')
                            ->image()
                            ->maxSize(512)
                            ->columnSpanFull(),

                    ])->columns(2),

                // ----------------------------------------------------
                // SEÇÃO 2 — INVENTÁRIO
                // ----------------------------------------------------
                Section::make('Inventário e Alertas')
                    ->icon('heroicon-o-archive-box')
                    ->collapsible()
                    ->schema([
                        TextInput::make('minimum_stock')
                            ->label('Stock Mínimo')
                            ->numeric()
                            ->default(5)
                            ->helperText('Quantidade mínima para considerar baixo stock.'),

                        TextInput::make('stock_alert_emails')
                            ->label('Emails para Alerta de Stock')
                            ->placeholder('email1@loja.com; email2@loja.com')
                            ->helperText('Pode inserir vários emails separados por ponto e vírgula (;).')
                            ->columnSpanFull(),
                    ])->columns(2),

                // ----------------------------------------------------
                // SEÇÃO 3 — CONTACTOS
                // ----------------------------------------------------
                Section::make('Contactos e Localização')
                    ->icon('heroicon-o-map-pin')
                    ->collapsible()
                    ->schema([

                        TextInput::make('support_phone')
                            ->label('Telefone de Suporte')
                            ->placeholder('Ex: 923 000 000'),

                        TextInput::make('company_email')
                            ->label('Email Geral')
                            ->email() // Valida que é um email válido
                            ->helperText('Este é o email principal da empresa. Insira apenas um endereço.'),

                        TextInput::make('support_email')
                            ->label('Email de Suporte')
                            ->placeholder('suporte@loja.com; admin@loja.com')
                            ->helperText('Pode inserir vários emails separados por ponto e vírgula (;).'),

                        TextInput::make('company_address')
                            ->label('Endereço'),

                        TextInput::make('company_city')
                            ->label('Cidade'),

                        TextInput::make('company_country')
                            ->label('País'),

                        TextInput::make('working_hours')
                            ->label('Horário de Funcionamento')
                            ->placeholder('Ex: Seg–Sab: 08h00–18h00')
                            ->columnSpanFull(),
                    ])->columns(2),

                // ----------------------------------------------------
                // SEÇÃO 4 — REDES SOCIAIS
                // ----------------------------------------------------
                Section::make('Redes Sociais')
                    ->icon('heroicon-o-share')
                    ->collapsible()
                    ->schema([

                        TextInput::make('facebook_url')
                            ->label('Facebook (Usuário)')
                            ->prefix('facebook.com/')
                            ->placeholder('ex: ezandu')
                            ->formatStateUsing(fn (?string $state) => str_replace('https://facebook.com/', '', $state))
                            ->dehydrateStateUsing(fn (?string $state) => $state ? 'https://facebook.com/' . $state : null),

                        TextInput::make('instagram_url')
                            ->label('Instagram (Usuário)')
                            ->prefix('instagram.com/')
                            ->placeholder('ex: ezandu')
                            ->formatStateUsing(fn (?string $state) => str_replace('https://instagram.com/', '', $state))
                            ->dehydrateStateUsing(fn (?string $state) => $state ? 'https://instagram.com/' . $state : null),

                        TextInput::make('whatsapp_url')
                            ->label('WhatsApp (Número)')
                            ->prefix('wa.me/')
                            ->placeholder('244923111222')
                            ->helperText('Formato: Código do país (244) + Número.')
                            ->formatStateUsing(fn (?string $state) => str_replace('https://wa.me/', '', $state))
                            ->dehydrateStateUsing(fn (?string $state) => $state ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $state) : null),

                    ])->columns(3),

                // ----------------------------------------------------
                // SEÇÃO 5 — IA / FAQs
                // ----------------------------------------------------
                Section::make('IA / FAQ')
                    ->description('Configuração de respostas automáticas do bot.')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsible()
                    ->schema([

                        Textarea::make('faq_who_answer')
                            ->label('Resposta: Quem é você?')
                            ->helperText('Texto usado quando perguntam "Quem é você?" ao bot.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        // Esta tabela fica vazia ou é suprimida porque você só gerencia uma única linha de configurações.
        return $table->columns([])->actions([])->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Garante que estamos sempre editando a mesma e única linha de configuração (singleton).
     */
    public static function getRecord(): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::firstOrNew([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            // Como este recurso é um singleton, 'edit' geralmente é substituído por ListSettings
            // para redirecionar diretamente para a edição.
            // Para manter a estrutura original com /edit:
            'edit' => Pages\EditSetting::route('/{record}/edit'), 
        ];
    }
}