<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters; 

class DeliveryLocationsChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; 

    protected static ?string $heading = 'Locais de Entrega (Pedidos Concluídos)';
    protected static ?string $maxHeight = '300px'; 
    protected static ?int $sort = 5; 

    // As props e listeners foram removidos (correto)

    protected function getData(): array
    {
        // ⭐️ CORREÇÃO APLICADA: Usar valores padrão (fallback)
        // Note que o filtro usa 'start_date' e 'end_date'
        $startDate = $this->filters['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $this->filters['end_date'] ?? Carbon::now()->toDateString();
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // (Restante da lógica do método getData() permanece igual)
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