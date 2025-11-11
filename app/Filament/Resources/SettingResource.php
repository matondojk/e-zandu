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
// Não precisamos do 'use Illuminate\Http\UploadedFile;'

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    // ... (configuração do menu) ...
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

                        // --- INÍCIO DA CORREÇÃO FINAL ---

                        FileUpload::make('logo_light_path')
                            ->label('Logo Claro (Modo Dia)')
                            ->disk('public')
                            ->directory('branding') // 1. Define o diretório
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024)
                            ->columnSpan(1)
                            // 2. Define o nome do ficheiro
                            ->getUploadedFileNameForStorageUsing(function () {
                                return 'logo-light.png';
                            }),

                        FileUpload::make('logo_dark_path')
                            ->label('Logo Escuro (Modo Noite)')
                            ->disk('public')
                            ->directory('branding') // 1. Define o diretório
                            ->visibility('public')
                            ->image()
                            ->maxSize(1024)
                            ->columnSpan(1)
                            // 2. Define o nome do ficheiro
                            ->getUploadedFileNameForStorageUsing(function () {
                                return 'logo-dark.png';
                            }),

                        FileUpload::make('icon_path')
                            ->label('Favicon (.png)')
                            ->disk('public')
                            ->directory('branding') // 1. Define o diretório
                            ->visibility('public')
                            ->image()
                            ->maxSize(512)
                            ->columnSpan(2)
                            // 2. Define o nome do ficheiro
                            ->getUploadedFileNameForStorageUsing(function () {
                                return 'favicon.png';
                            }),

                        // --- FIM DA CORREÇÃO FINAL ---

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
                    // ... (resto do seu formulário - sem alterações) ...
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
                    // ... (resto do seu formulário - sem alterações) ...
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

                        Textarea::make('faq_who_answer')
                            ->label('Resposta: Quem é você?')
                            ->rows(3)->required(),
                    ]),
            ]);
    }

    // ... (resto do ficheiro: table(), getRelations(), getRecord(), getPages() - sem alterações) ...
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
