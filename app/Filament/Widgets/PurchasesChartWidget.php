<?php

namespace App\Filament\Widgets;

use App\Models\Purchase; // Assumindo o Model Purchase
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PurchasesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Valor de Compras (Entradas de Estoque)';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

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

        // Consulta de Compras (Assumindo que Purchase tem 'total_amount' e 'created_at')
        $purchases = Purchase::query()
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as purchase_date'),
                DB::raw('SUM(total_amount) as total') // Assumir que existe um campo total_amount
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
                    'backgroundColor' => '#3b82f6', // Azul (Cor diferente de vendas)
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