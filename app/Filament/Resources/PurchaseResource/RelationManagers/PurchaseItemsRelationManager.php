<?php

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use App\Models\Product; 
use App\Models\PurchaseItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Number; // Não é mais necessário
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\Log; 

class PurchaseItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Itens da Compra';
    // ... (configuração) ...
    protected static ?string $modelLabel = 'Item de Compra';
    protected static ?string $pluralModelLabel = 'Itens de Compra';
    protected static ?string $recordTitleAttribute = 'product.name';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Produto')
                    ->required()
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->createOptionForm(fn (Form $form) => ProductResource::form($form)->getComponents())
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} (SKU: {$record->sku})")
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $product = Product::find($state);
                        if ($product) {
                            $set('unit_cost', $product->price); 
                            $set('unit', $product->unit ? $product->unit : 'Unidade');
                            
                            // ✅ LÓGICA DE IVA AUTOMÁTICO (Opcional, mas útil)
                            // Calcula 14% sobre o (custo * qtd)
                            $quantity = (int) $get('quantity') > 0 ? (int) $get('quantity') : 1;
                            $cost = (float) $product->price;
                            $set('iva_amount', ($cost * $quantity) * 0.14);
                        }
                    })
                    ->columnSpan(4), 

                Forms\Components\TextInput::make('unit_cost')
                    ->label('Custo Unitário (Kz)')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal')
                    ->prefix('Kz')
                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                    ->reactive() // Reativo para recalcular IVA
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $quantity = (int) $get('quantity') > 0 ? (int) $get('quantity') : 1;
                        $cost = (float) $state;
                        $discount = (float) $get('discount');
                        // IVA sobre (Custo - Desconto) * Qtd
                        $set('iva_amount', (($cost * $quantity) - $discount) * 0.14);
                    }),
                    
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade Comprada')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->dehydrateStateUsing(fn ($state) => (int) $state)
                    ->reactive() // Reativo para recalcular IVA
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $quantity = (int) $state > 0 ? (int) $state : 1;
                        $cost = (float) $get('unit_cost');
                        $discount = (float) $get('discount');
                        // IVA sobre (Custo * Qtd) - Desconto
                        $set('iva_amount', (($cost * $quantity) - $discount) * 0.14);
                    }),
                    
                Forms\Components\Select::make('unit')
                    ->label('Unidade de Compra')
                    ->options([
                        'Unidade' => 'Unidade',
                        'Caixa' => 'Caixa',
                        'Metro' => 'Metro',
                        'Kg' => 'Quilograma (Kg)',
                        'Litro' => 'Litro (L)',
                        'Pacote' => 'Pacote',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('discount')
                    ->label('Desconto do Item (Kz)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Kz')
                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                    ->reactive() // Reativo para recalcular IVA
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        $quantity = (int) $get('quantity') > 0 ? (int) $get('quantity') : 1;
                        $cost = (float) $get('unit_cost');
                        $discount = (float) $state;
                        // IVA sobre (Custo * Qtd) - Desconto
                        $set('iva_amount', (($cost * $quantity) - $discount) * 0.14);
                    }),

                // ✅ NOVO: Campo para IVA do Item
                Forms\Components\TextInput::make('iva_amount')
                    ->label('Valor de IVA do Item (Kz)')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->prefix('Kz')
                    ->helperText('IVA (14%) deste item. Ajuste se for isento (0).')
                    ->dehydrateStateUsing(fn ($state) => (float) $state),

                // ✅ CORREÇÃO: Aumentado para 3 linhas
                Forms\Components\Textarea::make('notes')
                    ->label('Notas (Opcional)')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(4); 
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->description(function (PurchaseItem $record): string {
                        $sku = $record->product ? $record->product->sku : 'N/A';
                        return "SKU: {$sku}";
                    }),
                
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->formatStateUsing(fn ($state, PurchaseItem $record) => number_format($state, 0, ',', '.') . " {$record->unit}"),

                Tables\Columns\TextColumn::make('unit_cost')
                    ->label('Custo Unitário')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd(),
                    
                Tables\Columns\TextColumn::make('discount')
                    ->label('Desconto Item')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd(),
                
                // ✅ NOVO: Coluna de IVA do Item
                Tables\Columns\TextColumn::make('iva_amount')
                    ->label('IVA Item')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd(),

                // ✅ ALTERADO: Usa o Accessor total_payable (Subtotal + IVA do item)
                Tables\Columns\TextColumn::make('total_payable') 
                    ->label('Custo Total (c/ IVA)')
                    ->formatStateUsing(fn ($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->weight('bold')
                    ->alignEnd(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Adicionar Produto')
                    ->before(function (array $data) {
                        Log::error('RM CreateAction::before - DATA:', $data); 
                    })
                    ->after(fn () => $this->dispatch('refresh-totals')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function (array $data) {
                        Log::error('RM EditAction::before - DATA:', $data); 
                    })
                    ->after(fn () => $this->dispatch('refresh-totals')),
                
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => $this->dispatch('refresh-totals')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}