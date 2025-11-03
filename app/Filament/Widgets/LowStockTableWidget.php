<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use App\Models\StockMovement; 
use App\Models\Setting;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; // Importar DB

class LowStockTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Alerta de Estoque Baixo';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected function getTableQuery(): Builder
    {
        // 1. Obter o limite mínimo de estoque das configurações
        $minimumStock = Cache::remember('min_stock', 60 * 60, function () {
            return Setting::first()?->minimum_stock ?? 10;
        });

        // 2. Subquery para calcular o estoque atual de cada produto
        $currentStockSubquery = StockMovement::selectRaw('SUM(quantity)')
            ->whereColumn('product_id', 'products.id')
            ->limit(1);

        // 3. Consulta principal: Adiciona o cálculo e filtra por ele (HAVING)
        return Product::query()
            // Adicionar a coluna calculada (nomeamos como 'current_stock')
            ->addSelect(['current_stock' => $currentStockSubquery]) 
            // Filtrar usando a coluna calculada
            ->having('current_stock', '<=', $minimumStock) 
            ->having('current_stock', '>', 0)
            ->orderByDesc('current_stock') 
            ->limit(10); 
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label('Produto')
                ->searchable(),
                // ->url(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record])),

            Tables\Columns\TextColumn::make('current_stock')
                ->label('Estoque Atual')
                ->badge()
                // Agora usamos a coluna 'current_stock' que acabámos de criar via subquery
                ->getStateUsing(fn ($record) => $record->current_stock ?? 0)
                ->color(fn ($record) => $record->current_stock <= 0 ? 'danger' : 'warning'), // ✅ VÍRGULA ADICIONADA AQUI!
                
            Tables\Columns\TextColumn::make('sku')
                ->label('SKU'),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}