<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Purchase; 
use App\Models\StockMovement; 
use App\Models\Setting;
use App\Models\Expense; // ✅ NOVO: Importar Model Expense
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

        // 2. Lógica de Compras (Custos de Mercadoria)
        $purchasesQuery = Purchase::whereBetween('document_date', [$start, $end]);
            // Assumindo que o status 'completed' ou 'delivered'
            // ->whereIn('status', ['completed', 'delivered']); 
        $totalPurchases = $purchasesQuery->sum('total_amount');

        // 3. ✅ NOVO: Lógica de Despesas Operacionais
        $totalExpenses = Expense::whereBetween('expense_date', [$start, $end])
            ->sum('amount');
        
        // 4. ✅ NOVO: Cálculo do Lucro Líquido
        $grossProfit = $totalRevenue - $totalPurchases; // Lucro Bruto
        $netProfit = $grossProfit - $totalExpenses; // Lucro Líquido
        
        // 5. Estoque Baixo (Lógica mantida)
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

        // --- ✅ CARDS FINANCEIROS REFINADOS ---
        return [
            // CARD 1: Receita Total (Vendas)
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Total de vendas concluídas no período')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            // CARD 2: Custos Totais (Mercadoria + Operacional)
            Stat::make('Custos Totais', number_format($totalPurchases + $totalExpenses, 0, ',', '.') . ' Kz')
                ->description("Compras (Kz $totalPurchases) + Despesas (Kz $totalExpenses)")
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning'),

            // CARD 3: Lucro Líquido (O MAIS IMPORTANTE)
            Stat::make('Lucro Líquido', number_format($netProfit, 0, ',', '.') . ' Kz')
                ->description('Receita - (Compras + Despesas)')
                ->descriptionIcon($netProfit >= 0 ? 'heroicon-m-banknotes' : 'heroicon-m-exclamation-triangle')
                ->color($netProfit >= 0 ? 'primary' : 'danger'),
            
            // CARD 4: Métricas Operacionais
            Stat::make('Pedidos / Estoque Baixo', number_format($totalOrders, 0, ',', '.') . ' / ' . number_format($lowStock, 0, ',', '.'))
                ->description('Total de pedidos concluídos / Alertas de estoque')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
        ];
    }
    
    public function render()
    {
        return view('filament.widgets.stats-overview-widget', [
            'stats' => $this->getStats(), 
        ]);
    }
}