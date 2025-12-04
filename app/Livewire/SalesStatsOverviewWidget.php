<?php

namespace App\Livewire; 

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cart;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Setting;
use App\Models\Expense;
use App\Models\SubscriptionHistory; // <--- Importante: Adicionar o Model
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Models\Product;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class SalesStatsOverviewWidget extends BaseWidget 
{
    use InteractsWithPageFilters; 

    protected function getStats(): array
    {
        // Filtros de Data
        $startDate = $this->filters['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $this->filters['end_date'] ?? Carbon::now()->toDateString();

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Lógica de Vendas (Receita)
        $salesQuery = Cart::whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end]);
            
        $revenueExpression = 'COALESCE(total_price, 0) + COALESCE(shipping_amount, 0) - COALESCE(discount_amount, 0)';
        $totalRevenue = $salesQuery->sum(DB::raw($revenueExpression));
        $totalOrders = $salesQuery->count();

        // 2. Lógica de Compras (Custos de Mercadoria - COGS)
        $purchasesQuery = Purchase::whereBetween('expected_delivery_date', [$start, $end]);
        $totalPurchases = $purchasesQuery->sum('total_amount');

        // 3. Lógica de Despesas Operacionais
        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])
            ->sum('amount');
        
        // 4. Lógica de Assinaturas (Novo)
        // Soma os pagamentos de assinatura feitos neste período
        $totalSubscriptions = SubscriptionHistory::whereBetween('created_at', [$start, $end])
            ->whereIn('event', ['subscription.activated', 'subscription.renewed'])
            ->sum('price'); // Assume que o campo 'price' é decimal/float

        // 5. Cálculo dos Totais
        $totalCosts = $totalPurchases + $totalExpenses + $totalSubscriptions; // Soma tudo nos custos

        $grossProfit = $totalRevenue - $totalPurchases; // Lucro Bruto (Receita - Custo Mercadoria)
        $netProfit = $totalRevenue - $totalCosts; // Lucro Líquido (Receita - Tudo)
        
        // 6. Estoque Baixo
        $minimumStock = Cache::remember('min_stock', 60 * 60, fn() => Setting::first()?->minimum_stock ?? 10);
        $lowStockQuery = Product::query()->select('id');
        $lowStock = $lowStockQuery->whereExists(function (QueryBuilder $query) use ($minimumStock) { 
                $query->select(DB::raw('SUM(T2.quantity)'))
                    ->from('stock_movements as T2')
                    ->whereColumn('T2.product_id', 'products.id')
                    ->having(DB::raw('SUM(T2.quantity)'), '<=', $minimumStock) 
                    ->having(DB::raw('SUM(T2.quantity)'), '>', 0);
            })
            ->count();
        
        // Formatação auxiliar para a descrição não ficar gigante no código
        $fmtPurchases = number_format($totalPurchases, 0, ',', '.');
        $fmtExpenses = number_format($totalExpenses, 0, ',', '.');
        $fmtSubs = number_format($totalSubscriptions, 0, ',', '.');

        // --- CARDS ---
        return [
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Total de vendas concluídas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('Custos Totais', number_format($totalCosts, 0, ',', '.') . ' Kz')
                ->description("Compras ($fmtPurchases) + Despesas ($fmtExpenses) + Assinatura ($fmtSubs)")
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'), // Custos geralmente representamos com cor de alerta ou neutra

            Stat::make('Lucro Líquido', number_format($netProfit, 0, ',', '.') . ' Kz')
                ->description('Receita - Custos Totais')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle')
                ->color($netProfit >= 0 ? 'primary' : 'danger'),
            
            Stat::make('Pedidos / Estoque Baixo', number_format($totalOrders, 0, ',', '.') . ' / ' . number_format($lowStock, 0, ',', '.'))
                ->description('Pedidos / Alertas')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
}