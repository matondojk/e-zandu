<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // Garanta que isto está aqui

class SalesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; // Garanta que isto está aqui

    protected static ?string $heading = 'Vendas por Dia';
    protected static ?int $sort = 1;
    protected static ?string $maxHeight = '300px';

    // Se existirem estas linhas, REMOVA-AS, pois agora usará InteractsWithPageFilters:
    // public ?string $startDate = null;
    // public ?string $endDate = null;
    // protected $listeners = ['dateRangeUpdated' => 'updateDateRange'];
    // public function updateDateRange($data): void { /* ... */ }

    protected function getData(): array
    {
        // ⭐️ CORREÇÃO: Adicionar valores padrão (fallback)
        $startDate = $this->filters['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $this->filters['end_date'] ?? Carbon::now()->toDateString();
        
        // Usar as variáveis seguras
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $period = Carbon::parse($start)->daysUntil($end);
        
        // (Restante da lógica do método getData() permanece igual)
        // ...
        $dates = [];
        $labels = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
            $labels[] = $date->format('d/m');
        }

        $sales = Cart::query()
            ->whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end])
            // ... (restante da query)
            ->select(
                DB::raw('DATE(updated_at) as sale_date'),
                DB::raw('SUM(total_price) as total')
            )
            ->groupBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $data = array_merge($dates, $sales->map->total->toArray());
        
        return [
            'datasets' => [
                [
                    'label' => 'Receita (Kz)',
                    'data' => array_values($data),
                    'backgroundColor' => '#f97316', 
                    'borderColor' => '#f97316',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}