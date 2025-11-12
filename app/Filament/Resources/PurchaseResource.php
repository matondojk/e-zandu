<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Blade;
use App\Models\Setting;
use Filament\Forms\Components\Repeater;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Compras (Entrada de Stock)';
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $pluralModelLabel = 'Compras';
    protected static ?string $navigationGroup = 'Catálogo & Inventário';
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'compras';

    public static function form(Form $form): Form
    {
        // O seu método form() mantém-se igual ao que você enviou
        return $form
            ->schema([
                Forms\Components\Section::make('Dados da Compra')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Geral')
                            ->options([
                                'Aberta' => 'Aberta',
                                'Confirmada' => 'Confirmada',
                                'Recebida' => 'Recebida',
                                'Cancelada' => 'Cancelada',
                            ])
                            ->default('Aberta')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function (Set $set, $state) {
                                if ($state === 'Recebida') {
                                    $set('is_delivered', true);
                                } else {
                                    $set('is_delivered', false);
                                }
                            }),

                        Forms\Components\Select::make('user_id')
                            ->label('Responsável')
                            ->relationship('user', 'name')
                            ->default(fn() => Auth::id())
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('supplier_id')
                            ->label('Fornecedor')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn(Form $form) => SupplierResource::form($form)->getComponents())
                            ->columnSpan(1),

                        Forms\Components\Select::make('stock_id')
                            ->label('Armazém de Entrada')
                            ->relationship('stock', 'name')
                            ->required()
                            ->default(function () {
                                $defaultStock = Stock::where('is_default', true)->first();
                                return $defaultStock ? $defaultStock->id : null;
                            })
                            ->searchable()
                            ->preload()
                            ->columnSpan(2),
                    ]),

                Forms\Components\Section::make('Prazos e Pagamento')
                    ->columns(4)
                    ->schema([
                        Forms\Components\DatePicker::make('issue_date')
                            ->label('Data de Emissão')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('expected_delivery_date')
                            ->label('Previsão de Entrega'),

                        Forms\Components\Select::make('payment_terms')
                            ->label('Condições Pagamento')
                            ->options([
                                'Pronto Pagamento' => 'Pronto Pagamento',
                                '15 Dias' => '15 Dias',
                                '30 Dias' => '30 Dias',
                                '60 Dias' => '60 Dias',
                                '50% Entrada / 50% Entrega' => '50% Entrada / 50% Entrega',
                            ])
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('payment_method')
                            ->label('Forma de Pagamento')
                            ->options([
                                'Transferência Bancária' => 'Transferência Bancária',
                                'Pagamento na Entrega' => 'Pagamento na Entrega',
                            ])
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('Configurações e Anexos')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Group::make()
                            ->columnSpan(1)
                            ->schema([
                                Forms\Components\Hidden::make('is_delivered')
                                    ->dehydrated()
                                    ->default(false),

                                Forms\Components\Toggle::make('calculate_iva')
                                    ->label('Calcular IVA (14%)?')
                                    ->default(true)
                                    ->helperText('Se ativo, calcula 14% sobre o total.')
                                    ->reactive(),

                                Forms\Components\TextInput::make('freight_cost')
                                    ->label('Frete (Kz)')
                                    ->numeric()
                                    ->prefix('Kz')
                                    ->default(0),
                            ]),

                        Forms\Components\Textarea::make('internal_notes')
                            ->label('Observações Internas')
                            ->rows(3)
                            ->columnSpan(2),
                        
                        // Se você mudou para RelationManager, pode remover este Repeater daqui também
                        // para não ficar duplicado (no form e na relação)
                        // Mas se quiser manter para o momento da criação, deixe como está.
                    ]),

                Forms\Components\Section::make('Resumo Financeiro')
                    ->columns(4)
                    ->schema([
                        Placeholder::make('total_amount_display')
                            ->label('Subtotal (Itens)')
                            ->content(fn(?Purchase $record) => number_format($record?->total_amount ?? 0, 2, ',', '.') . ' Kz'),

                        Placeholder::make('discount_total_display')
                            ->label('Descontos')
                            ->content(fn(?Purchase $record) => number_format($record?->discount_total ?? 0, 2, ',', '.') . ' Kz'),

                        Placeholder::make('iva_amount_display')
                            ->label('IVA (14%)')
                            ->content(fn(?Purchase $record) => number_format($record?->iva_amount ?? 0, 2, ',', '.') . ' Kz'),

                        Placeholder::make('total_payable_display')
                            ->label('TOTAL A PAGAR')
                            ->content(function (?Purchase $record): HtmlString {
                                $total = number_format($record?->total_payable ?? 0, 2, ',', '.') . ' Kz';
                                return new HtmlString('<span style="font-size: 1.25rem; font-weight: 800; color: #16a34a;">' . $total . '</span>');
                            })
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('discount_total')->default(0),
                        Forms\Components\Hidden::make('total_amount')->default(0),
                        Forms\Components\Hidden::make('iva_amount')->default(0),
                        Forms\Components\Hidden::make('total_payable')->default(0),
                    ])
                    ->hidden(fn(?Purchase $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Baixar Relatório')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();
                        $settings = Setting::first();
                        $pdf = Pdf::loadView('pdfs.purchases-list', [
                            'records' => $records,
                            'settings' => $settings
                        ])->setPaper('a4', 'landscape');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'relatorio-compras-' . date('Y-m-d') . '.pdf');
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('purchase_number')
                    ->label('Nº Compra')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fornecedor')
                    ->searchable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label('Emissão')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Aberta' => 'gray',
                        'Confirmada' => 'info',
                        'Recebida' => 'success',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_payable')
                    ->label('Total')
                    ->formatStateUsing(fn($state) => number_format($state, 2, ',', '.') . ' Kz')
                    ->weight('bold')
                    ->alignEnd(),

                Tables\Columns\IconColumn::make('is_delivered')
                    ->label('Entregue')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock'),

                // ❌ REMOVIDO: Tables\Columns\IconColumn::make('attachments')
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Aberta' => 'Aberta',
                        'Confirmada' => 'Confirmada',
                        'Recebida' => 'Recebida',
                        'Cancelada' => 'Cancelada',
                    ]),
                Tables\Filters\SelectFilter::make('supplier')
                    ->relationship('supplier', 'name'),
                Tables\Filters\Filter::make('issue_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('De'),
                        Forms\Components\DatePicker::make('date_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn($q) => $q->whereDate('issue_date', '>=', $data['date_from']))
                            ->when($data['date_until'], fn($q) => $q->whereDate('issue_date', '<=', $data['date_until']));
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->action(function (Purchase $record) {
                        $record->load(['items.product', 'supplier', 'stock', 'user']);
                        $settings = Setting::first();
                        $pdf = Pdf::loadView('pdfs.purchase-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'pedido-' . $record->purchase_number . '.pdf');
                    }),
                Tables\Actions\ViewAction::make()->iconButton(),
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PurchaseItemsRelationManager::class,
            RelationManagers\PurchaseAttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
            'view' => Pages\ViewPurchase::route('/{record}'),
        ];
    }
}