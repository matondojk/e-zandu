<?php

namespace App\Filament\Widgets;

use App\Models\Purchase; 
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ⭐️ NOVO
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchasesChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; // ⭐️ NOVO

    protected static ?string $heading = 'Valor de Compras (Entradas de Estoque)';
    protected static ?int $sort = 4;
    // protected int | string | array $columnSpan = 6; // Pode ser removido

    // ❗️ REMOVIDO: $startDate, $endDate, $listeners, updateDateRange()

    protected function getData(): array
    {
        // ⭐️ ACESSO AOS FILTROS VIA $this->filters
        $start = $this->filters['startDate'] ?? Carbon::now()->subDays(30)->toDateString();
        $end = $this->filters['endDate'] ?? Carbon::now()->toDateString();

        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->endOfDay();
        $period = Carbon::parse($start)->daysUntil($end);
        
        $dates = [];
        $labels = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
            $labels[] = $date->format('d/m');
        }

        // Consulta de Compras
        $purchases = Purchase::query()
            ->whereBetween('document_date', [$start, $end]) // Uso correto do document_date
            ->select(
                DB::raw('DATE(document_date) as purchase_date'), 
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
                    'backgroundColor' => '#3b82f6', // Azul
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