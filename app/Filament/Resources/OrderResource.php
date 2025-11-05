<?php

namespace App\Filament\Resources;

// ✅ IMPORTS ESSENCIAIS
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Cart;
use App\Models\Setting;
use App\Models\PaymentMethod; // ✅ NOVO: Para filtro
use App\Models\ShippingRate; // ✅ NOVO: Para filtro
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
    protected static ?string $slug = 'vendas'; // URL em português

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

        return $table
            ->columns([

                // ✅ Coluna de Cliente (Null-Safe)
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->state(fn(Cart $record): ?string => $record->user?->name ?? 'Cliente Anónimo')
                    ->searchable(
                        query: fn(Builder $query, string $search): Builder =>
                        $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    )
                    ->sortable(),

                // ✅ Coluna de Telefone (Null-Safe)
                Tables\Columns\TextColumn::make('user.phone')
                    ->label('WhatsApp')
                    ->state(fn(Cart $record): ?string => $record->user?->phone)
                    ->placeholder('N/A')
                    ->searchable(
                        query: fn(Builder $query, string $search): Builder =>
                        $query->whereHas('user', fn($q) => $q->where('phone', 'like', "%{$search}%"))
                    )
                    ->copyable()
                    ->copyMessage('Número copiado!'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Pagamento')
                    ->colors([
                        'warning' => 'active',
                        'success' => fn($state) => in_array($state, ['paid', 'completed']),
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

                // ✅ Coluna Total Geral (Com cálculo de frete e desconto dinâmicos)
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Geral')
                    ->state(
                        fn(Cart $record): float => ($record->total_price + $record->shipping_amount) - $record->discount_amount
                    )
                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->weight('bold')
                    ->sortable(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Pedido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([

                // --- FILTROS EXISTENTES ---

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Pendente',
                        'paid' => 'Pago',
                        'completed' => 'Concluído',
                    ])
                    ->label('Filtrar por Status Pagamento'),

                // --- NOVOS FILTROS ---

                // ✅ 1. Filtro de Método de Pagamento
                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name') // Usa a relação paymentMethod
                    ->label('Método de Pagamento'),

                // ✅ 2. Filtro de Local de Entrega
                Tables\Filters\SelectFilter::make('shipping_rate_id')
                    ->relationship('shippingRate', 'province_name') // Usa a relação shippingRate
                    ->label('Local de Entrega'),

                // ✅ 3. Filtro de Status de Entrega
                Tables\Filters\SelectFilter::make('delivery_status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Em Processamento',
                        'delivered' => 'Entregue',
                    ])
                    ->label('Status Entrega'),

                // ✅ 4. Filtro de Cupão Utilizado
                Tables\Filters\Filter::make('coupon')
                    ->label('Cupão Utilizado')
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['is_used'] ?? false) {
                            return $query->whereNotNull('coupon_id');
                        }
                        if (($data['is_not_used'] ?? false)) {
                            return $query->whereNull('coupon_id');
                        }
                        return $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if ($data['is_used'] ?? false) {
                            return 'Cupão: Utilizado';
                        }
                        if ($data['is_not_used'] ?? false) {
                            return 'Cupão: Não Utilizado';
                        }
                        return null;
                    })
                    ->form([
                        Forms\Components\Toggle::make('is_used')
                            ->label('Usou um cupão'),
                        Forms\Components\Toggle::make('is_not_used')
                            ->label('Não usou cupão'),
                    ]),

                // --- FILTRO DE DATA EXISTENTE ---
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
                Tables\Actions\Action::make('contact_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('success')
                    ->url(
                        fn(Cart $record): ?string =>
                        $record->user?->phone
                            ? "https://wa.me/{$record->user->phone}?text=" . urlencode("Olá! Sou da E-Zandu, sobre o seu pedido #{$record->id}.")
                            : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip('Abrir conversa no WhatsApp Web')
                    ->iconButton()
                    ->visible(fn(Cart $record): bool => !empty($record->user?->phone)),

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
        // ✅ ATUALIZADO: Carrega as relações para os filtros
        return parent::getEloquentQuery()->with(['items.product', 'user', 'coupon', 'shippingRate', 'paymentMethod']);
    }
}
