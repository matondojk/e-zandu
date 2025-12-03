<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Cart;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Vendas';
    protected static ?string $modelLabel = 'Venda';
    protected static ?string $pluralModelLabel = 'Vendas';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'cart_number';
    protected static ?string $navigationGroup = 'Loja & Vendas';
    protected static ?string $slug = 'vendas';

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'active')->count();
        return $count > 0 ? $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('export_excel_custom')
                    ->label('Exportar Excel')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->iconButton()
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();

                        return Excel::download(
                            new OrderExport($query),
                            'relatorio-vendas-' . date('Y-m-d') . '.xlsx'
                        );
                    }),

                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Baixar Relatório (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.orders-list', [
                            'records' => $records,
                            'settings' => $settings
                        ])->setPaper('a4', 'landscape');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'relatorio-vendas-' . date('Y-m-d') . '.pdf');
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('cart_number')
                    ->label('Nº Pedido')
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cliente')
                    ->state(fn(Cart $record): ?string => $record->user?->name ?? 'Cliente Anónimo')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.phone')
                    ->label('WhatsApp')
                    ->state(fn(Cart $record): ?string => $record->user?->phone)
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status Pagamento')
                    ->colors([
                        'warning' => 'active',
                        'success' => fn($state) => in_array($state, ['paid', 'completed']),
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => 'Pendente',
                        'cancelled' => 'Cancelado',
                        'paid' => 'Pago',
                        'completed' => 'Concluído',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                // Coluna Total Geral
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Geral')
                    ->state(fn(Cart $record): float => ($record->total_price + $record->shipping_amount) - $record->discount_amount)
                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                // NOVO: Colunas de Auditoria (Usuários)
                Tables\Columns\TextColumn::make('paymentConfirmedBy.name')
                    ->label('Pago Por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('deliveryConfirmedBy.name')
                    ->label('Entregue Por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('rejectedBy.name')
                    ->label('Rejeitado Por')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                // NOVO: Colunas de Auditoria (Timestamps)
                Tables\Columns\TextColumn::make('payment_confirmed_at')
                    ->label('Data Pagamento')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('delivery_confirmed_at')
                    ->label('Data Entrega')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('rejected_at')
                    ->label('Data Rejeição')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Pendente',
                        'paid' => 'Pago',
                        'completed' => 'Concluído',
                        'cancelled' => 'Cancelado',
                    ])
                    ->label('Status Pagamento'),

                Tables\Filters\SelectFilter::make('payment_method_id')
                    ->relationship('paymentMethod', 'name')
                    ->label('Método de Pagamento'),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('De'),
                        Forms\Components\DatePicker::make('created_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn(Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn(Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->action(function (Cart $record) {
                        $record->load(['items.product', 'user', 'shippingRate', 'paymentMethod']);
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.order-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'pedido-' . ($record->cart_number ?? $record->id) . '.pdf');
                    }),

                Tables\Actions\Action::make('contact_whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('success')
                    ->url(fn(Cart $record) => $record->user?->phone ? "https://wa.me/{$record->user->phone}" : null)
                    ->openUrlInNewTab()
                    ->iconButton()
                    ->visible(fn(Cart $record) => !empty($record->user?->phone)),

                Tables\Actions\ViewAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        return parent::getEloquentQuery()->with([
            'items.product', 'user', 'coupon', 'shippingRate', 'paymentMethod',
            'paymentConfirmedBy', 'deliveryConfirmedBy', 'rejectedBy' 
        ]);
    }
}