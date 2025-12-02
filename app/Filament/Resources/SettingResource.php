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
use Filament\Forms\Components\Toggle;

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
                // SEÇÃO 1 — GERAL
                // ----------------------------------------------------
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

                        // ---- NOVOS CAMPOS DE CONTACTO ----

                        TextInput::make('support_phone')
                            ->label('Telefone de Suporte')
                            ->placeholder('Ex: 923 000 000'),

                        TextInput::make('company_email')
                            ->label('Email Geral da Empresa'),

                        TextInput::make('support_email')
                            ->label('Email de Suporte'),

                        TextInput::make('company_address')
                            ->label('Endereço da Empresa'),

                        TextInput::make('company_city')
                            ->label('Cidade'),

                        TextInput::make('company_country')
                            ->label('País'),

                        TextInput::make('working_hours')
                            ->label('Horário de Funcionamento')
                            ->placeholder('Ex: Seg–Sab 08h às 18h'),

                    ])->columns(2),

                // ----------------------------------------------------
                // SEÇÃO 2 — REDES SOCIAIS
                // ----------------------------------------------------
                Section::make('Redes Sociais')
                    ->icon('heroicon-o-share')
                    ->collapsible()
                    ->schema([
                        TextInput::make('facebook_url')->label('Facebook'),
                        TextInput::make('instagram_url')->label('Instagram'),
                        TextInput::make('whatsapp_url')->label('WhatsApp Link (wa.me)')
                    ])->columns(3),

                // ----------------------------------------------------
                // SEÇÃO 3 — PAGAMENTOS
                // ----------------------------------------------------
                Section::make('Métodos de Pagamento')
                    ->icon('heroicon-o-credit-card')
                    ->collapsible()
                    ->schema([

                        Toggle::make('enable_bank_transfer')
                            ->label('Ativar Transferência Bancária'),

                        TextInput::make('bank_account_holder')
                            ->label('Titular da Conta'),

                        TextInput::make('bank_account_number')
                            ->label('Número da Conta'),

                        TextInput::make('bank_iban')
                            ->label('IBAN'),

                        Toggle::make('enable_express_payment')
                            ->label('Ativar Pagamento Expresso (ex: Multicaixa Express)'),

                        TextInput::make('express_payment_phone')
                            ->label('Telefone para Pagamento Expresso'),

                        Toggle::make('enable_cash_on_delivery')
                            ->label('Ativar Pagamento na Entrega'),
                    ])->columns(2),

                // ----------------------------------------------------
                // SEÇÃO 4 — IA / FAQs
                // ----------------------------------------------------
                Section::make('IA / Respostas do Bot (FAQs)')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsible()
                    ->schema([

                        Textarea::make('company_description')
                            ->label('Descrição da Empresa')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('faq_who_answer')
                            ->label('Resposta: Quem é você?')
                            ->rows(3)
                            ->required(),

                        Textarea::make('faq_locations_answer')
                            ->label('Resposta: Onde entregam?')
                            ->rows(3),

                        Textarea::make('faq_time_answer')
                            ->label('Resposta: Tempo de entrega')
                            ->rows(3),

                        Textarea::make('faq_payment_answer')
                            ->label('Resposta: Métodos de pagamento')
                            ->rows(3),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([])->actions([])->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getRecord(): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::first([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
