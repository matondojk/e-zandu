<?php

namespace App\Filament\Resources\CustomerResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model; // ✅ 1. IMPORTAR A CLASSE MODEL

class CustomerStatsOverview extends BaseWidget
{
    // ✅ 2. DECLARAR A PROPRIEDADE PÚBLICA 'record'
    // A página ViewCustomer irá injetar o cliente automaticamente aqui.
    public ?Model $record = null;

    protected function getStats(): array
    {
        /** @var Customer $customer */
        $customer = $this->record; 

        if (!$customer) {
            return [];
        }

        // A lógica de cálculo está correta
        $paidCarts = $customer->carts()
            ->whereIn('status', ['paid', 'completed'])
            ->get();

        $totalSpent = $paidCarts->sum('total_price');
        $totalOrders = $paidCarts->count();
        $avgTicket = ($totalOrders > 0) ? ($totalSpent / $totalOrders) : 0;

        return [
            Stat::make('Gasto Total Vitalício', number_format($totalSpent, 0, ',', '.') . ' Kz')
                ->description('Total de compras concluídas')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            
            Stat::make('Total de Pedidos', $totalOrders)
                ->description('Pedidos pagos ou concluídos')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Ticket Médio', number_format($avgTicket, 0, ',', '.') . ' Kz')
                ->description('Média gasta por pedido')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),
        ];
    }
}