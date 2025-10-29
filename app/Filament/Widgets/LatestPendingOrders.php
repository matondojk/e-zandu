<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\OrderResource;
use App\Models\Cart;

class LatestPendingOrders extends BaseWidget
{
    // --- ALTERAÇÃO AQUI ---
    protected static ?int $sort = 2; // 1. Mover para baixo (ordem 2)
    // --- FIM DA ALTERAÇÃO ---

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Cart::where('status', 'active')->orderBy('created_at', 'desc')->limit(10)
            )
            ->heading('Últimos Pedidos Pendentes (Aguardando Aprovação)')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Pedido'),
                
                Tables\Columns\TextColumn::make('user_phone')
                    ->label('Cliente (WhatsApp)')
                    ->icon('heroicon-m-phone')
                    ->copyable(),

                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Kz ' . number_format($state, 0, ',', '.')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Pedido')
                    ->since(),
                
                Tables\Columns\IconColumn::make('payment_proof_path')
                    ->label('Comprovativo?')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('Ver Pedido')
                    ->url(fn (Cart $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}