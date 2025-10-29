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
use Filament\Tables\Columns; // <-- NOVO/CORRIGIDO: Importação correta para Colunas da Tabela
use Illuminate\Support\Facades\Log;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    // Configuração do Menu
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Configurações da Loja';
    protected static ?string $modelLabel = 'Configuração';
    protected static ?string $pluralModelLabel = 'Configurações';
    protected static ?int $navigationSort = 100; // Põe no final do menu
    protected static ?string $navigationGroup = 'Sistema & Config';

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

                        // NOVO CAMPO: NIF
                        TextInput::make('nif')
                            ->label('NIF/Identificação Fiscal')
                            ->placeholder('Ex: 5000000000')
                            ->maxLength(50)
                            ->required(),
                            
                        // LOGO e ÍCONE
                        FileUpload::make('logo_path')
                            ->label('Logotipo da Loja')
                            ->disk('public') // Grava em storage/app/public/
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
                            ->email()
                            ->required(),
                    ])->columns(2), // Adicionado colunas para Logo e Icone
                
                // --- Secção 2: Pagamentos e Frete ---
                Section::make('Pagamentos & Frete')
                    ->description('Dados bancários e custos de entrega.')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible()
                    ->schema([
                        TextInput::make('iban')
                            ->label('IBAN (para Transferência)')
                            ->required(),
                        TextInput::make('multicaixa_express')
                            ->label('Nº Multicaixa Express')
                            ->required(),
                            
                        // NOVO CAMPO: TAXA DE FRETE
                        TextInput::make('shipping_rate')
                            ->label('Taxa de Frete Padrão (Kz)')
                            ->numeric()
                            ->step(100)
                            ->default(0)
                            ->helperText('Valor fixo cobrado por pedido (Ex: 1000 Kz).')
                            ->required(),
                    ])->columns(3), // Usa 3 colunas para pagamentos/frete

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
                            ->suggestions([ 
                                'Roupas', 'Calçados', 'Acessórios', 'Electrónicos', 'Cosméticos',
                            ])
                            ->placeholder('Adicione categorias (Ex: Roupas) e pressione Enter')
                            ->helperText('Adicione os tipos de produtos que a IA deve mencionar.')
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('faq_who_answer')
                            ->label('Resposta: Quem é você?')
                            ->rows(3)->required(),
                        Textarea::make('faq_locations_answer')
                            ->label('Resposta: Locais de entrega?')
                            ->rows(3)->required(),
                        Textarea::make('faq_time_answer')
                            ->label('Resposta: Tempo de entrega?')
                            ->rows(3)->required(),
                        Textarea::make('faq_payment_answer')
                            ->label('Resposta: Métodos de pagamento?')
                            ->rows(3)->required(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        // Forçar o uso de Columns\TextColumn (devido ao import corrigido)
        return $table
            ->query(Setting::where('id', 1))
            ->columns([
                Columns\TextColumn::make('store_name')->label('Nome da Loja'),
                Columns\TextColumn::make('nif')->label('NIF'),
                Columns\TextColumn::make('shipping_rate')->label('Frete'),
                Columns\TextColumn::make('updated_at')
                    ->label('Última Modificação')
                    ->dateTime('d/m/Y H:i')
                    ->since(),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(), // Usar o namespace completo aqui ou Tables\Actions
            ])
            ->bulkActions([])
            ->headerActions([]); 
    }
    
    public static function getRelations(): array
    {
        return [];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'), 
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
    
    public static function getRecord(): ?\Illuminate\Database\Eloquent\Model
    {
         // Tenta encontrar o registro de configuração (assumindo ID=1)
         return static::getModel()::find(1);
    }
}
