<?php

namespace App\Filament\Widgets;

use App\Models\Cart;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryLocationsChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Locais de Entrega (Pedidos Concluídos)';
    protected static ?string $maxHeight = '300px'; // Manter a altura padrão
    protected static ?int $sort = 5; // Ordem no dashboard

    // Props e Listener para o filtro
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

        // Consulta que junta Carts com ShippingRates
        $data = Cart::query()
            ->join('shipping_rates', 'carts.shipping_rate_id', '=', 'shipping_rates.id')
            ->whereIn('carts.status', ['completed', 'paid']) // Apenas pedidos pagos/concluídos
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
        // 'doughnut' (rosquinha) fica bem ao lado do 'pie' (pizza)
        return 'doughnut'; 
    }
}