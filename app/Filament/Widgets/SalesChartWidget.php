<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Vendas por Dia';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    // ✅ Props e Listener para o filtro
    public ?string $startDate = null;
    public ?string $endDate = null;
    protected $listeners = ['dateRangeUpdated' => 'updateDateRange'];

    public function updateDateRange($data): void
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        $this->dispatch('$refresh'); // Atualiza o widget
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?? Carbon::now()->subDays(30)->toDateString())->startOfDay();
        $end = Carbon::parse($this->endDate ?? Carbon::now()->toDateString())->endOfDay();
        $period = Carbon::parse($start)->daysUntil($end);
        
        // Inicializa arrays com datas no período
        $dates = [];
        $labels = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
            $labels[] = $date->format('d/m');
        }

        // Consulta de Vendas
        $sales = Cart::query()
            ->whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end])
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
                    'backgroundColor' => '#f97316', // Laranja
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