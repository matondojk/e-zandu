<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Purchase; 
use App\Models\StockMovement; 
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB; 
use Illuminate\Database\Query\Builder as QueryBuilder;

class SalesStatsOverviewWidget extends Component
{
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected $listeners = [
        'dateRangeUpdated' => 'updateDateRange',
    ];

    public function mount(?string $startDate = null, ?string $endDate = null): void
    {
        $this->startDate = $startDate ?? Carbon::now()->subDays(30)->toDateString();
        $this->endDate = $endDate ?? Carbon::now()->toDateString();
    }

    public function updateDateRange($data): void
    {
        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
    }
    
    protected function getStats(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // 1. Lógica de Vendas (Receita)
        $salesQuery = Cart::whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end]);
        $totalRevenue = $salesQuery->sum('total_price');
        $totalOrders = $salesQuery->count();

        // 2. Lógica de Compras (Custos)
        $purchasesQuery = Purchase::whereBetween('document_date', [$start, $end]);
        $totalPurchases = $purchasesQuery->sum('total_amount');

        // 3. Lucro Bruto e Métricas
        // ✅ CORREÇÃO: Lucro Bruto = Vendas - Compras
        $grossProfit =  $totalPurchases - $totalRevenue; 
        $avgTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        
        // 4. Estoque Baixo 
        $minimumStock = Cache::remember('min_stock', 60 * 60, function () {
            return Setting::first()?->minimum_stock ?? 10;
        });
        
        $lowStockQuery = Product::query()
            ->select('id');
        
        $lowStock = $lowStockQuery->whereExists(function (QueryBuilder $query) use ($minimumStock) { 
                $query->select(DB::raw('SUM(T2.quantity)'))
                    ->from('stock_movements as T2')
                    ->whereColumn('T2.product_id', 'products.id')
                    ->having(DB::raw('SUM(T2.quantity)'), '<=', $minimumStock) 
                    ->having(DB::raw('SUM(T2.quantity)'), '>', 0);
            })
            ->count();

        return [
            // CARD 1: Receita Total (Vendas)
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Vendas completas no período')
                // ✅ COR LARANJA
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'), 
            
            // CARD 2: Total de Compras (Custo)
            Stat::make('Total de Compras', number_format($totalPurchases, 0, ',', '.') . ' Kz')
                ->description('Custo total de mercadorias compradas')
                // ✅ COR LARANJA
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('primary'), 

            // CARD 3: Lucro Bruto (Diferença)
            Stat::make('Lucro Bruto', number_format($grossProfit, 0, ',', '.') . ' Kz')
                ->description('Vendas - Compras (Margem Bruta)')
                // Mantém a cor condicional para o lucro, o que é importante para o gestor.
                ->descriptionIcon($grossProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-arrow-trending-down')
                ->color($grossProfit >= 0 ? 'success' : 'danger'), // Sucesso para lucro, Danger para prejuízo
            
            // CARD 4: Métricas Operacionais
            Stat::make('Pedidos / Estoque Baixo', number_format($totalOrders, 0, ',', '.') . ' / ' . number_format($lowStock, 0, ',', '.'))
                ->description('Total de pedidos / Produtos em alerta de estoque')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'), // Mantemos Warning para risco de estoque
        ];
    }

    public function render()
    {
        return view('filament.widgets.stats-overview-widget', [
            'stats' => $this->getStats(),
        ]);
    }
}
