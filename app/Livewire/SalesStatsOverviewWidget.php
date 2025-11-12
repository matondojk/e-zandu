<?php

namespace App\Livewire; 

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cart;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Setting;
use App\Models\Expense;
use Illuminate\Support\Carbon; // Importar Carbon
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
        // ⭐️ CORREÇÃO 1: Adicionar valores padrão (fallback) para os filtros
        $startDate = $this->filters['start_date'] ?? Carbon::now()->subDays(30)->toDateString();
        $endDate = $this->filters['end_date'] ?? Carbon::now()->toDateString();

        // Usar as variáveis seguras
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // 1. Lógica de Vendas (Receita)
        $salesQuery = Cart::whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end]);
            
        $revenueExpression = 'COALESCE(total_price, 0) + COALESCE(shipping_amount, 0) - COALESCE(discount_amount, 0)';
        $totalRevenue = $salesQuery->sum(DB::raw($revenueExpression));
        $totalOrders = $salesQuery->count();

        // 2. Lógica de Compras (Custos de Mercadoria)
        $purchasesQuery = Purchase::whereBetween('expected_delivery_date', [$start, $end]);
        $totalPurchases = $purchasesQuery->sum('total_amount');

        // 3. Lógica de Despesas Operacionais
        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])
            ->sum('amount');
        
        // 4. Cálculo do Lucro Líquido
        $grossProfit = $totalRevenue - $totalPurchases; 
        $netProfit = $grossProfit - $totalExpenses; 
        
        // 5. Estoque Baixo (Lógica inalterada)
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
        
        // --- CARDS ---
        return [
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Total de vendas concluídas no período')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
            
            // ⭐️ CORREÇÃO 2: Usar $totalPurchases em vez de $purchasesCost
            Stat::make('Custos Totais', number_format($totalPurchases + $totalExpenses, 0, ',', '.') . ' Kz')
                ->description("Compras (Kz " . number_format($totalPurchases, 0, ',', '.') . ") + Despesas (Kz " . number_format($totalExpenses, 0, ',', '.') . ")")
                ->descriptionIcon('heroicon-m-arrow-trending-down'),

            Stat::make('Lucro Líquido', number_format($netProfit, 0, ',', '.') . ' Kz')
                ->description('Receita - (Compras + Despesas)')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle')
                ->color($netProfit >= 0 ? 'primary' : 'danger'),
            
            Stat::make('Pedidos / Estoque Baixo', number_format($totalOrders, 0, ',', '.') . ' / ' . number_format($lowStock, 0, ',', '.'))
                ->description('Total de pedidos concluídos / Alertas de estoque')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
}