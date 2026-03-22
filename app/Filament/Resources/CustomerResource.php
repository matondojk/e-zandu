<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
// ✅ Importar as Relações
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms; // ✅ Importar Forms
use Filament\Forms\Form; // ✅ Importar Form

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $slug = 'clientes'; // URL em português
    protected static ?string $navigationGroup = 'Clientes';

    /**
     * ✅ Adiciona os campos de CRM (Tags e Notas) ao formulário de Edição
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informação Pessoal')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone (WhatsApp)')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Endereço de Entrega')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                // ✅ NOVA SECÇÃO DE CRM
                Forms\Components\Section::make('Atributos de CRM')
                    ->schema([
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags de Segmentação')
                            ->placeholder('Ex: VIP, Revendedor, Cliente Novo...'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Notas Internas')
                            ->rows(5)
                            ->placeholder('Adicione notas sobre este cliente...'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // O seu ficheiro original tinha esta query
                Customer::query()->where('role', 'customer')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Ocultar email

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone / WhatsApp')
                    ->searchable(),

                // ✅ Nova Coluna de Tags
                Tables\Columns\TagsColumn::make('tags')
                    ->label('Tags')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registro')
                    ->dateTime('d/m/Y') // Simplificado
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('contact_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('success')
                    ->url(
                        fn(Customer $record): string =>
                        "https://wa.me/{$record->phone}?text=" .
                            // ✅ Atualizado o nome da loja
                            urlencode("Olá {$record->name}! Aqui é da E-Zandu.")
                    )
                    ->openUrlInNewTab()
                    ->visible(fn(Customer $record): bool => filled($record->phone))
                    ->tooltip('Abrir conversa no WhatsApp')
                    ->iconButton(), // Mais limpo

                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([]);
    }

    /**
     * ✅ Regista o Relation Manager
     */
    public static function getRelations(): array
    {
        return [
            RelationManagers\CartsRelationManager::class,
        ];
    }

    /**
     * ✅ Regista as páginas, incluindo a nova 'View'
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'), // Vamos assumir que criamos clientes aqui
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
            'view' => Pages\ViewCustomer::route('/{record}'), // ✅ NOVA PÁGINA
        ];
    }
}
