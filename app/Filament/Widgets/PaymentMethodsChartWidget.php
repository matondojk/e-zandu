<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; // ⭐️ NOVO
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentMethodsChartWidget extends ChartWidget
{
    use InteractsWithPageFilters; // ⭐️ NOVO

    protected static ?string $heading = 'Métodos de Pagamento (Pedidos Concluídos)';
    protected static ?int $sort = 3; 
    protected static ?string $maxHeight = '300px';
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
            ->join('payment_methods', 'carts.payment_method_id', '=', 'payment_methods.id')
            ->whereIn('carts.status', ['completed', 'paid'])
            ->whereBetween('carts.updated_at', [$start, $end])
            ->select(
                'payment_methods.name as payment_name',
                DB::raw('COUNT(carts.id) as total')
            )
            ->groupBy('payment_methods.name')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total de Pedidos',
                    'data' => $data->map(fn ($item) => $item->total),
                    'backgroundColor' => ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'],
                ],
            ],
            'labels' => $data->map(fn ($item) => $item->payment_name),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}