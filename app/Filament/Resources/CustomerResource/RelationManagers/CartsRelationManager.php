<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Filament\Resources\OrderResource; // ✅ Para o link
use App\Models\Cart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CartsRelationManager extends RelationManager
{
    protected static string $relationship = 'carts';

    protected static ?string $title = 'Histórico de Pedidos';
    protected static ?string $modelLabel = 'Pedido';
    protected static ?string $pluralModelLabel = 'Pedidos';

    public function form(Form $form): Form
    {
        return $form->schema([]); // Não permitir criar/editar pedidos por aqui
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID do Pedido')
                    ->searchable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'active',
                        'success' => fn ($state) => in_array($state, ['paid', 'completed']),
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Pendente',
                        'paid' => 'Pago',
                        'completed' => 'Concluído',
                        default => ucfirst($state),
                    }),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Valor Total')
                    ->money('Kz')
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data do Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Desabilitado
            ])
            ->actions([
                // ✅ Ação para ver o pedido no OrderResource
                Tables\Actions\ViewAction::make()
                    ->url(fn (Cart $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->iconButton(),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}