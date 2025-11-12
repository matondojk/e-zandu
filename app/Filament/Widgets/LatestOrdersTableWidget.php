<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Cart;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ⭐️ NOVO
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

class LatestOrdersTableWidget extends BaseWidget
{
    use InteractsWithPageFilters; // ⭐️ NOVO

    protected static ?string $heading = 'Pedidos em Trânsito/Entregues';
    protected static ?int $sort = 2;
    // protected int | string | array $columnSpan = 6; // Pode ser removido

    // ❗️ REMOVIDO: $startDate, $endDate, $listeners, updateDateRange()

    protected function getTableQuery(): Builder
    {
        $query = Cart::query()
            ->whereIn('status', ['paid', 'completed']) 
            ->where('delivery_status', '!=', 'pending') // Filtro: Diferente de pending
            ->orderByDesc('created_at');
        
        // ⭐️ ACESSO AOS FILTROS VIA $this->filters
        $start = $this->filters['startDate'] ?? null;
        $end = $this->filters['endDate'] ?? null;
        
        if ($start && $end) {
            $start = Carbon::parse($start)->startOfDay();
            $end = Carbon::parse($end)->endOfDay();
            $query->whereBetween('created_at', [$start, $end]);
        }
        
        return $query->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('Pedido #')
                ->sortable()
                ->url(fn (Cart $record): string => OrderResource::getUrl('view', ['record' => $record])),
                
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