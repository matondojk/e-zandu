<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\Concerns\InteractsWithPageFilters; 

class PurchasesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; 

    protected static ?string $heading = 'Valor de Compras (Entradas de Estoque)';
    protected static ?int $sort = 4;
    
    // As props e listeners foram corretamente removidos graças ao InteractsWithPageFilters

    protected function getData(): array
    {
        // ⭐️ CORREÇÃO APLICADA: Usar valores padrão (fallback)
        $startDate = $this->filters['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $this->filters['end_date'] ?? Carbon::now()->toDateString();

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

        $purchases = Purchase::query()
            ->whereBetween('expected_delivery_date', [$start, $end]) 
            ->select(
                DB::raw('DATE(expected_delivery_date) as purchase_date'), 
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('purchase_date')
            ->get()
            ->keyBy('purchase_date');

        $data = array_merge($dates, $purchases->map->total->toArray());

        return [
            'datasets' => [
                [
                    'label' => 'Compras (Kz)',
                    'data' => array_values($data),
                    'backgroundColor' => '#3b82f6',
                    'borderColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}