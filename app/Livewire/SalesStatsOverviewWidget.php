<?php

namespace App\Livewire; // Ou onde quer que esteja o seu widget

// 1. MUDANÇA PRINCIPAL: Usar o Widget correto do Filament
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cart;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Setting;
use App\Models\Expense;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder as QueryBuilder;
use App\Models\Product;

// 2. MUDANÇA PRINCIPAL: Extends 'BaseWidget' (que é o StatsOverviewWidget)
class SalesStatsOverviewWidget extends BaseWidget 
{
    // 3. ADICIONAR O LISTENER AQUI
    protected $listeners = [
        'dateRangeUpdated' => 'updateDateRange',
    ];
    
    // 4. ADICIONAR AS PROPS AQUI
    public ?string $startDate = null;
    public ?string $endDate = null;

    // 5. O SEU MÉTODO 'mount()' (estava em AnalyticsDashboard.php, mas deve estar aqui)
    public function mount(): void
    {
        $this->startDate = Carbon::now()->subDays(30)->toDateString();
        $this->endDate = Carbon::now()->toDateString();
    }
    
    // 6. O SEU MÉTODO 'updateDateRange()'
    public function updateDateRange($data): void
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
        // $refresh já não é necessário, o Filament trata disso
    }

    // 7. O SEU MÉTODO getStats() (com cores ajustadas)
    protected function getStats(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. Lógica de Vendas (Receita)
        $salesQuery = Cart::whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end]);
            
        $revenueExpression = 'COALESCE(total_price, 0) + COALESCE(shipping_amount, 0) - COALESCE(discount_amount, 0)';
        $totalRevenue = $salesQuery->sum(DB::raw($revenueExpression));
        $totalOrders = $salesQuery->count();

        // 2. Lógica de Compras (Custos de Mercadoria)
        $purchasesQuery = Purchase::whereBetween('document_date', [$start, $end]);
        $totalPurchases = $purchasesQuery->sum('total_amount');

        // 3. Lógica de Despesas Operacionais
        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])
            ->sum('amount');
        
        // 4. Cálculo do Lucro Líquido
        $grossProfit = $totalRevenue - $totalPurchases; 
        $netProfit = $grossProfit - $totalExpenses; 
        
        // 5. Estoque Baixo
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
        
        // --- CARDS COM CORES DISCRETAS ---
        return [
            // CARD 1: Receita Total (Sem cor, fica cinza/discreto)
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Total de vendas concluídas no período')
                ->descriptionIcon('heroicon-m-arrow-trending-up'),
                // ->color('success') REMOVIDO
            
            // CARD 2: Custos Totais (Sem cor, fica cinza/discreto)
            Stat::make('Custos Totais', number_format($totalPurchases + $totalExpenses, 0, ',', '.') . ' Kz')
                ->description("Compras (Kz " . number_format($totalPurchases, 0, ',', '.') . ") + Despesas (Kz " . number_format($totalExpenses, 0, ',', '.') . ")")
                ->descriptionIcon('heroicon-m-arrow-trending-down'),
                // ->color('warning') REMOVIDO

            // CARD 3: Lucro Líquido (Este MANTÉM A COR, pois é importante)
            Stat::make('Lucro Líquido', number_format($netProfit, 0, ',', '.') . ' Kz')
                ->description('Receita - (Compras + Despesas)')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle')
                ->color($netProfit >= 0 ? 'primary' : 'danger'),
            
            // CARD 4: Métricas (Já estava 'gray', que é discreto)
            Stat::make('Pedidos / Estoque Baixo', number_format($totalOrders, 0, ',', '.') . ' / ' . number_format($lowStock, 0, ',', '.'))
                ->description('Total de pedidos concluídos / Alertas de estoque')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
    
    // 8. REMOVER O MÉTODO render()
    // O 'BaseWidget' trata do render por si só.
}