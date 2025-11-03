<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Cart;
use App\Models\Setting;
use App\Services\InvoiceService;
use App\Services\WhatsAppService;
use Exception;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Number; // Removido pois não está sendo usado
use Illuminate\Support\Facades\Cache;
use Filament\Tables\Filters; // ✅ ADICIONADO: Para o filtro de datas

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

    // ✅ Pedido 1: Título do grupo mudado
    protected static ?string $navigationGroup = 'Loja & Vendas';

    // ✅ Pedido 2: Badge para pedidos "active"
    public static function getNavigationBadge(): ?string
    {
        // Conta apenas os pedidos que precisam de ação (status 'active')
        $count = static::getModel()::where('status', 'active')->count();
        return $count > 0 ? $count : null;
    }

    // Desabilitar formulários de "Criar" e "Editar"
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        $shippingRate = Cache::remember('shipping_rate', 60 * 5, function () {
            $settings = Setting::find(1);
            return $settings->shipping_rate ?? 0;
        });

        return $table
            ->columns([
                // ✅ Pedido 3: Coluna ID Removida

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Número copiado!'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'active',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Pendente',
                        'paid' => 'Pago',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Geral')
                    ->state(fn(Cart $record): float => $record->total_price + $shippingRate)
                    // ✅ Formato corrigido para 2 casas decimais e Kz no fim
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
                    ])
                    ->label('Filtrar por Status'),

                // ✅ Pedido 6: Filtro de Data (semelhante aos outros)
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
                    ->label('Falar no WhatsApp')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->url(fn(Cart $record): string => "https://wa.me/{$record->user->phone}?text=" . urlencode("Olá! Sou da TangaWA Shop, sobre o seu pedido #{$record->id}."), true)
                    ->openUrlInNewTab()
                    ->tooltip('Abrir conversa no WhatsApp Web')
                    ->iconButton()
                    ->visible(fn(Cart $record): bool => !empty($record->user?->phone)), // ✅ Só mostra se o cliente tiver telefone

                Tables\Actions\ViewAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // ✅ Pedido 4: Ordenar por 'updated_at' (mais recente primeiro)
            ->defaultSort('updated_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        // Puxar a taxa de frete para o Infolist
        $shippingRate = Cache::remember('shipping_rate', 60 * 5, function () {
            $settings = Setting::find(1);
            return $settings->shipping_rate ?? 0;
        });

        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detalhes do Cliente')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')->label('Cliente')->icon('heroicon-o-user'),
                        Infolists\Components\TextEntry::make('user.phone')
                            ->label('WhatsApp')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('user.delivery_address')
                            ->label('Endereço de Entrega')
                            ->default('Não fornecido')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('Resumo Financeiro')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('id')->label('ID Pedido'), // O ID ainda aparece aqui
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'active' => 'warning',
                                'paid' => 'success',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Subtotal (Produtos)')
                            // ✅ Formato corrigido
                            ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz'),

                        Infolists\Components\TextEntry::make('shipping_rate_display')
                            ->label('Taxa de Frete')
                            // ✅ Formato corrigido
                            ->state(fn(Cart $record) => number_format($shippingRate, 2, ',', '.') . ' Kz'),

                        Infolists\Components\TextEntry::make('grand_total')
                            ->label('TOTAL GERAL (Pago pelo Cliente)')
                            // ✅ Formato corrigido
                            ->state(fn(Cart $record) => number_format($record->total_price + $shippingRate, 2, ',', '.') . ' Kz')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->color('success')
                            ->weight('bold'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Data do Pedido')
                            ->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Última Atualização')
                            ->dateTime('d/m/Y H:i'),
                    ]),

                // Secção para o Comprovativo (Só aparece se tiver um)
                Infolists\Components\Section::make('Pagamento')
                    ->visible(fn(Cart $record) => !empty($record->payment_proof_path))
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_proof_path')
                            ->label('Comprovativo')
                            ->formatStateUsing(function ($state): ?HtmlString {
                                if (!$state) return null;
                                $url = Storage::disk('public')->url($state);
                                return new HtmlString('<a href="' . $url . '" target="_blank" style="color: #25D366; text-decoration: underline;">Ver Comprovativo</a>');
                            })
                            ->icon('heroicon-m-paper-clip'),
                    ]),
            ]);
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
        return parent::getEloquentQuery()->with(['items.product', 'user']);
    }
}
