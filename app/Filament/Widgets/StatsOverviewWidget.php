<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cart; // Importar o Model Cart
use App\Models\Product; // Importar o Model Product
use Illuminate\Support\Number; // Para formatar Kz

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Calcular os totais
        $totalRevenue = Cart::where('status', 'paid')->sum('total_price');
        $pendingOrders = Cart::where('status', 'active')->count();
        $paidOrders = Cart::where('status', 'paid')->count();
        $totalProducts = Product::count();

        return [
            // Card 1: Receita Total
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Total de pedidos pagos')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Gráfico de exemplo

            // Card 2: Pedidos Pendentes
            Stat::make('Pedidos Pendentes', $pendingOrders)
                ->description('Pedidos aguardando aprovação')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            // Card 3: Pedidos Pagos
            Stat::make('Total de Pedidos Pagos', $paidOrders)
                ->description('Pedidos concluídos com sucesso')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
                
            // Card 4: Total de Produtos
            Stat::make('Produtos no Catálogo', $totalProducts)
                ->description('Total de itens na loja')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('gray'),
        ];
    }
}