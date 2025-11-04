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
                            
                            // ✅ REMOVIDO: Cálculo de IVA individual
                        }
                    })
                    ->columnSpanFull(), 

                Forms\Components\TextInput::make('unit_cost')
                    ->label('Custo Unitário (Kz)')
                    ->numeric()
                    ->required()
                    ->inputMode('decimal')
                    ->prefix('Kz')
                    ->dehydrateStateUsing(fn ($state) => (float) $state)
                    ->reactive() 
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                        // ✅ REMOVIDO: Cálculo de IVA individual
                    }),
                    
                Forms\Components\TextInput::make('quantity')
                    ->label('Quantidade Comprada')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->minValue(1)
                    ->dehydrateStateUsing(fn ($state) => (int) $state)
                    ->reactive() 
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                         // ✅ REMOVIDO: Cálculo de IVA individual
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
                    ->reactive() 
                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                         // ✅ REMOVIDO: Cálculo de IVA individual
                    }),

                // ✅ REMOVIDO: Campo 'iva_amount'
                // Forms\Components\TextInput::make('iva_amount') ...

                Forms\Components\Textarea::make('notes')
                    ->label('Notas (Opcional)')
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->columns(['default' => 1, 'md' => 4]); 
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
                
                // ✅ REMOVIDO: Coluna 'iva_amount'
                // Tables\Columns\TextColumn::make('iva_amount') ...

                // ✅ ALTERADO: 'total_payable' substituído por um subtotal de linha calculado
                Tables\Columns\TextColumn::make('line_subtotal') 
                    ->label('Subtotal Item')
                    ->state(function (PurchaseItem $record): float {
                        // Calcula (Qtd * Custo) - Desconto
                        return ($record->quantity * $record->unit_cost) - $record->discount;
                    })
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