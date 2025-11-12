<?php

namespace App\Filament\Widgets;

use App\Models\Purchase;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchasesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Valor de Compras (Entradas de Estoque)';
    protected static ?int $sort = 4;
    
    // ✅ Props e Listener para o filtro
    public ?string $startDate = null;
    public ?string $endDate = null;
    protected $listeners = ['dateRangeUpdated' => 'updateDateRange'];

    public function updateDateRange($data): void
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        $this->dispatch('$refresh');
    }

    protected function getData(): array
    {
        $start = Carbon::parse($this->startDate ?? Carbon::now()->subDays(30)->toDateString())->startOfDay();
        $end = Carbon::parse($this->endDate ?? Carbon::now()->toDateString())->endOfDay();
        $period = Carbon::parse($start)->daysUntil($end);
        
        $dates = [];
        $labels = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
            $labels[] = $date->format('d/m');
        }

        // ❗️ CORREÇÃO AQUI: Usar 'document_date' em vez de 'created_at'
        $purchases = Purchase::query()
            ->whereBetween('document_date', [$start, $end]) // <-- Corrigido
            ->select(
                DB::raw('DATE(document_date) as purchase_date'), // <-- Corrigido
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