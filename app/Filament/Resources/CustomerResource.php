<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $pluralLabel = 'Clientes';
    protected static ?string $slug = 'customers';
    protected static ?string $navigationGroup = 'Loja & Vendas';

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Customer::query()->where('role', 'customer')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone / WhatsApp'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('contact_whatsapp')
                    ->label('Falar no WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('success')
                    ->url(fn(Customer $record): string =>
                        "https://wa.me/{$record->phone}?text=" .
                        urlencode("Olá {$record->name}! Aqui é da loja TangaWA Shop.")
                    )
                    ->openUrlInNewTab()
                    ->visible(fn(Customer $record): bool => filled($record->phone))
                    ->tooltip('Abrir conversa no WhatsApp'),

                Tables\Actions\ViewAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([]); // sem ações em massa
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
        ];
    }
}
