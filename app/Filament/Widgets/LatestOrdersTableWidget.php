<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Cart;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Filament\Widgets\Concerns\InteractsWithPageFilters; 

class LatestOrdersTableWidget extends BaseWidget
{
    use InteractsWithPageFilters; 

    protected static ?string $heading = 'Pedidos em Trânsito/Entregues';
    protected static ?int $sort = 2;

    // A propriedade $filters, usada pelo trait, é definida internamente.

    protected function getTableQuery(): Builder
    {
        // ⭐️ CORREÇÃO ESSENCIAL: Usar valores padrão (fallback) para evitar o erro "array offset on value of type null"
        // Para uma tabela de "Últimos Pedidos", definimos null como padrão para não filtrar a data na carga inicial
        $startDate = $this->filters['start_date'] ?? null; 
        $endDate = $this->filters['end_date'] ?? null;

        $query = Cart::query()
            ->whereIn('status', ['paid', 'completed']) 
            ->where('delivery_status', '!=', 'pending') 
            ->orderByDesc('created_at');
        
        // Aplicar filtro de data APENAS se ambas as datas forem fornecidas (após a primeira atualização do filtro)
        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]); 
        }
        
        return $query->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Data do Pedido')
                ->date('d/m/Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Cliente')
                ->searchable(),

            Tables\Columns\TextColumn::make('delivery_status')
                ->label('Entrega')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'shipped' => 'info',
                    'delivered' => 'success',
                    'failed' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => ucfirst($state)),

            Tables\Columns\TextColumn::make('status')
                ->label('Pagamento') 
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'completed' => 'success',
                    'paid' => 'info',
                    'active' => 'warning',
                    'canceled' => 'danger',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('total_price')
                ->label('Total')
                ->formatStateUsing(fn ($state): string => Number::currency($state, 'Kz', 'pt_AO'))
                ->alignEnd(),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}