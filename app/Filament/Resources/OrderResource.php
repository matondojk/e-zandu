<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Cart;
use App\Models\Setting;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class OrderResource extends Resource
{
    protected static ?string $model = Cart::class;

    // --- Configurações de UI ---
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Vendas';
    protected static ?string $modelLabel = 'Venda';
    protected static ?string $pluralModelLabel = 'Vendas';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationGroup = 'Loja & Vendas';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'active')->count();
        return $count > 0 ? $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Total de pendentes';
    }


    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        // ❌ REMOVIDO: A busca obsoleta pelo $shippingRate estático
        
        return $table
            ->columns([
                
                // ✅ CORRIGIDO: user -> user (Null-Safe)
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->state(fn (Cart $record): ?string => $record->user?->name ?? 'Cliente Anónimo')
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    )
                    ->sortable(),

                // ✅ CORRIGIDO: user -> user (Null-Safe)
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('WhatsApp')
                    ->state(fn (Cart $record): ?string => $record->user?->phone)
                    ->placeholder('N/A')
                    ->searchable(query: fn (Builder $query, string $search): Builder => 
                        $query->whereHas('user', fn ($q) => $q->where('phone', 'like', "%{$search}%"))
                    )
                    ->copyable()
                    ->copyMessage('Número copiado!'),

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
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Desconto')
                    ->money('Kz')
                    ->alignEnd()
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                // ✅ CORRIGIDO: Grand Total agora usa $record->shipping_amount
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Geral')
                    ->state(fn(Cart $record): float => 
                        ($record->total_price + $record->shipping_amount) - $record->discount_amount
                    )
                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->weight('bold')
                    ->sortable(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Pendente',
                        'paid' => 'Pago',
                        'completed' => 'Concluído',
                    ])
                    ->label('Filtrar por Status'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data Pedido (De)'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data Pedido (Até)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                // ✅ CORRIGIDO: user -> user na Ação do WhatsApp
                Tables\Actions\Action::make('contact_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('success')
                    ->url(fn(Cart $record): ?string => 
                        $record->user?->phone // Usa o user, não user
                            ? "https://wa.me/{$record->user->phone}?text=" . urlencode("Olá! Sou da E-Zandu, sobre o seu pedido #{$record->id}.") 
                            : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip('Abrir conversa no WhatsApp Web')
                    ->iconButton()
                    ->visible(fn(Cart $record): bool => !empty($record->user?->phone)), // Usa o user, não user

                Tables\Actions\ViewAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        // Esta função é ignorada, mas vamos mantê-la limpa.
        return $infolist->schema([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        // ✅ ATUALIZADO: Carrega as novas relações 'shippingRate' e 'paymentMethod'
        return parent::getEloquentQuery()->with(['items.product', 'user', 'coupon', 'shippingRate', 'paymentMethod']);
    }
}