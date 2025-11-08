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
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    // Configuração do Menu
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Personalização';
    protected static ?string $modelLabel = 'Configuração';
    protected static ?string $pluralModelLabel = 'Configurações';
    protected static ?int $navigationSort = 100;
    protected static ?string $slug = 'personalizacao'; // URL mais limpa


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // --- Secção 1: Geral ---
                Section::make('Geral')
                    ->description('Configurações principais da loja e contatos.')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsible()
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nome da Loja')
                            ->required(),
                        TextInput::make('nif')
                            ->label('NIF/Identificação Fiscal')
                            ->placeholder('Ex: 5000000000')
                            ->maxLength(50)
                            ->required(),
                        FileUpload::make('logo_path')
                            ->label('Logotipo da Loja')
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024)
                            ->columnSpan(1),
                        FileUpload::make('icon_path')
                            ->label('Ícone (Favicon)')
                            ->disk('public')
                            ->directory('settings')
                            ->visibility('public')
                            ->image()
                            ->maxSize(512)
                            ->columnSpan(1),
                        TextInput::make('admin_phone')
                            ->label('Nº WhatsApp Admin (Notificações)')
                            ->helperText('Ex: 244932932532. Usado para enviar alertas de novos pedidos.')
                            ->required(),
                        TextInput::make('support_email')
                            ->label('Email de Suporte (Notificações)')
                            ->helperText('Emails de relatórios automáticos. Separar por ponto e vírgula (;) se houver mais de um.')
                            ->required(),
                    ])->columns(2),

                // --- Secção 2: Gestão de Stock e Alertas ---
                Section::make('Gestão de Stock e Alertas')
                    ->description('Definições para alertas automáticos de stock.')
                    ->icon('heroicon-o-archive-box')
                    ->collapsible()
                    ->schema([
                        TextInput::make('minimum_stock')
                            ->label('Nível Mínimo de Stock para Alerta')
                            ->numeric()
                            ->default(10)
                            ->helperText('A IA enviará um alerta quando o stock de um produto for inferior a este número.')
                            ->required(),
                        TextInput::make('stock_alert_emails')
                            ->label('Emails para Alertas de Stock')
                            ->helperText('Separar múltiplos emails com ponto e vírgula ( ; ).')
                            ->columnSpan(2),
                    ])->columns(3),

                // --- Secção 3: IA / Respostas do Bot (FAQs) ---
                Section::make('IA / Respostas do Bot (FAQs)')
                    ->description('Contexto e respostas que o bot de IA usará para perguntas comuns.')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsible()
                    ->schema([
                        Textarea::make('company_description')
                            ->label('Descrição da Empresa (para IA)')
                            ->rows(4)
                            ->helperText('O que é a empresa? Qual a vossa missão? A IA usará isto para "O que vocês fazem?".')
                            ->required()
                            ->columnSpanFull(),
                        TagsInput::make('product_types')
                            ->label('Tipos de Produtos Vendidos (para IA)')
                            ->suggestions(['Roupas', 'Calçados', 'Acessórios', 'Electrónicos', 'Cosméticos'])
                            ->placeholder('Adicione categorias (Ex: Roupas) e pressione Enter')
                            ->helperText('Adicione os tipos de produtos que a IA deve mencionar.')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('faq_who_answer')
                            ->label('Resposta: Quem é você?')
                            ->rows(3)->required(),
                        // Os campos 'Locais de entrega', 'Tempo de entrega' e 'Métodos de pagamento' foram removidos.
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getRecord(): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::firstOrCreate([], []);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
