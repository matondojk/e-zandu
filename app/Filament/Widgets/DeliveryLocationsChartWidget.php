<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ⭐️ NOVO
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryLocationsChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; // ⭐️ NOVO

    protected static ?string $heading = 'Locais de Entrega (Pedidos Concluídos)';
    protected static ?string $maxHeight = '300px'; 
    protected static ?int $sort = 5; 
    // protected int | string | array $columnSpan = 6; // Pode ser removido

    // ❗️ REMOVIDO: $startDate, $endDate, $listeners, updateDateRange()

    protected function getData(): array
    {
        // ⭐️ ACESSO AOS FILTROS VIA $this->filters
        $start = $this->filters['startDate'] ?? Carbon::now()->subDays(30)->toDateString();
        $end = $this->filters['endDate'] ?? Carbon::now()->toDateString();
        
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();

        $data = Cart::query()
            ->join('shipping_rates', 'carts.shipping_rate_id', '=', 'shipping_rates.id')
            ->whereIn('carts.status', ['completed', 'paid']) 
            ->whereBetween('carts.updated_at', [$start, $end])
            ->select(
                'shipping_rates.province_name',
                DB::raw('COUNT(carts.id) as total')
            )
            ->groupBy('shipping_rates.province_name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Entregas',
                    'data' => $data->map(fn ($item) => $item->total),
                    'backgroundColor' => ['#36A2EB', '#FFCE56', '#4BC0C0', '#FF6384', '#9966FF'],
                ],
            ],
            'labels' => $data->map(fn ($item) => $item->province_name),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; 
    }
}