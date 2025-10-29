<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Cart;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Carbon\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Pedidos nos Últimos 7 Dias';

    // --- ALTERAÇÕES AQUI ---
    protected static ?int $sort = 1; // 1. Mover para cima (ordem 1)
    protected int | string | array $columnSpan = 'full'; // 2. Ocupar largura total
    // --- FIM DAS ALTERAÇÕES ---

    protected function getData(): array
    {
        $data = Trend::model(Cart::class)
            ->between(
                start: now()->subDays(6),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Pedidos por Dia',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(37, 211, 102, 0.5)', // Verde WhatsApp
                    'borderColor' => 'rgb(37, 211, 102)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d/m')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}