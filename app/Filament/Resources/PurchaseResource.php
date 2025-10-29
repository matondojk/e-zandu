<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // ✅ ADICIONADO
use Illuminate\Database\Eloquent\Model;
use App\Models\Stock; 
// use Illuminate\Support\Number; // Removido
use Illuminate\Support\Facades\Log; 
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString; // Importar HtmlString
use Filament\Tables\Filters; // ✅ ADICIONADO

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;
    // ... (configuração) ...
    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Compras (Entrada de Stock)';
    protected static ?string $modelLabel = 'Compra';
    protected static ?string $pluralModelLabel = 'Compras';
    protected static ?string $navigationGroup = 'Catálogo & Inventário';
    protected static ?int $navigationSort = 6;


    public static function form(Form $form): Form
    {
        // ... (O MÉTODO FORM ESTÁ CORRETO E MANTIDO IGUAL) ...
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes da Compra')
                    ->columns(3)
                    ->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Fornecedor')
                            ->relationship('supplier', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm(fn (Form $form) => SupplierResource::form($form)->getComponents()), 
                            
                        Forms\Components\Select::make('stock_id')
                            ->label('Armazém Destino')
                            ->relationship('stock', 'name')
                            ->required()
                            ->default(function () {
                                $defaultStock = Stock::where('is_default', true)->first();
                                return $defaultStock ? $defaultStock->id : null;
                            })
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('document_date')
                            ->label('Data do Documento')
                            ->required()
                            ->default(now()),

                        Forms\Components\TextInput::make('document_number')
                            ->label('Nº Fatura/Documento')
                            ->maxLength(255)
                            ->columnSpan(1),
                            
                        Forms\Components\Toggle::make('is_delivered')
                            ->label('Material já foi entregue?')
                            ->default(true)
                            ->columnSpan(1)
                            ->inline(false) 
                            ->helperText('Se marcado, a movimentação de estoque será registada imediatamente.'),

                        Forms\Components\TextInput::make('freight_cost')
                            ->label('Custo do Frete (Kz)')
                            ->numeric()
                            ->default(0)
                            ->prefix('Kz')
                            ->inputMode('decimal')
                            ->reactive() 
                            ->afterStateUpdated(function (Model $record, $state, Forms\Contracts\HasForms $livewire) {
                                if ($record) {
                                    $record->freight_cost = (float) $state;
                                    $record->save(); 
                                    $livewire->dispatch('refresh-totals'); 
                                }
                            })
                            ->hidden(fn (?Purchase $record) => $record === null),
                    ]),

                Forms\Components\Section::make('Resumo Financeiro')
                    ->columns(4)
                    ->schema([
                        Placeholder::make('total_amount_display') 
                            ->label('Subtotal (Itens)')
                            ->content(function (?Purchase $record): string {
                                return number_format($record?->total_amount ?? 0, 2, ',', '.') . ' Kz';
                            }),

                        Placeholder::make('discount_total_display')
                            ->label('Desconto Total (Itens)')
                            ->content(function (?Purchase $record): string {
                                return number_format($record?->discount_total ?? 0, 2, ',', '.') . ' Kz';
                            }),
                        
                        Placeholder::make('freight_cost_display')
                            ->label('Frete')
                            ->content(function (?Purchase $record): string {
                                return number_format($record?->freight_cost ?? 0, 2, ',', '.') . ' Kz';
                            }),

                        Placeholder::make('iva_amount_display')
                            ->label('IVA (Total)') // O IVA agora é a soma dos itens
                            ->content(function (?Purchase $record): string {
                                return number_format($record?->iva_amount ?? 0, 2, ',', '.') . ' Kz';
                            }),
                        
                        Placeholder::make('total_payable_display')
                            ->label('Total Final (c/ IVA e Frete)')
                            ->content(function (?Purchase $record): HtmlString {
                                $total = number_format($record?->total_payable ?? 0, 2, ',', '.') . ' Kz';
                                return new HtmlString(
                                    '<span style="font-size: 1.25rem; font-weight: 700;">' . $total . '</span>'
                                );
                            })
                            ->columnSpanFull(),
                            
                        Forms\Components\Hidden::make('discount_total')->default(0),
                        Forms\Components\Hidden::make('total_amount')->default(0),
                        Forms\Components\Hidden::make('iva_amount')->default(0),
                        Forms\Components\Hidden::make('total_payable')->default(0),
                    ])
                    ->hidden(fn (?Purchase $record) => $record === null), 
                
                Forms\Components\Section::make('Produtos Comprados')
                    ->schema([
                         Forms\Components\Placeholder::make('items_placeholder')
                             ->content(fn (?Purchase $record) => $record ? 'Itens carregados no separador abaixo.' : 'Salve a compra para adicionar itens.'),
                    ])
                    ->hidden(fn (?Purchase $record) => $record === null) 
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            // ✅ Pedido 5: Ordenar por 'updated_at' (mais recente primeiro)
            ->defaultSort('updated_at', 'desc')
            
            ->columns([
                // ✅ Pedido 1: ID da Compra Removido
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Data Doc.')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Fornecedor')
                    ->searchable()
                    ->sortable(),

                // ✅ Pedido 2: Adicionar coluna Desconto Total
                Tables\Columns\TextColumn::make('discount_total')
                    ->label('Total Desconto')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->sortable(),

                // ✅ Pedido 2: Adicionar coluna Frete
                Tables\Columns\TextColumn::make('freight_cost')
                    ->label('Frete')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->sortable(),
                
                // ✅ Pedido 2: Adicionar coluna IVA
                Tables\Columns\TextColumn::make('iva_amount')
                    ->label('Total IVA')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_payable')
                    ->label('Valor Total (Pago)')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_delivered')
                    ->label('Entregue')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Escondido por defeito
            ])
            
            // ✅ Pedido 3: Adicionar mais filtros
            ->filters([
                Tables\Filters\Filter::make('document_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data Doc. (De)'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data Doc. (Até)'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '<=', $date),
                            );
                    }),
                Tables\Filters\SelectFilter::make('supplier')
                    ->label('Fornecedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\TernaryFilter::make('is_delivered')
                    ->label('Estado da Entrega')
                    ->boolean()
                    ->trueLabel('Entregues')
                    ->falseLabel('Pendentes')
                    ->native(false),
            ])
            
            // ✅ Pedido 4: Ações como ícones
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
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