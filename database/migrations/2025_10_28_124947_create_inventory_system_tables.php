<?php

namespace App\Livewire;

use App\Models\Cart;
use App\Models\Product;
use App\Models\StockMovement; // ✅ Adicionar Import
use App\Models\Setting;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder; // Adicionar Import

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
        $this->dispatch('$refresh');
    }
    
    protected function getStats(): array
    {
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->endOfDay();

        // Base Query para Vendas Concluídas
        $salesQuery = Cart::whereIn('status', ['completed', 'paid'])
            ->whereBetween('updated_at', [$start, $end]);

        // 1. Receita Total
        $totalRevenue = $salesQuery->sum('total_price');

        // 2. Número de Pedidos
        $totalOrders = $salesQuery->count();

        // 3. Produtos em Estoque Baixo (Lógica CORRIGIDA COM SUBQUERY)
        $minimumStock = Cache::remember('min_stock', 60 * 60, function () {
            return Setting::first()?->minimum_stock ?? 10;
        });
        
        // Subquery para calcular o estoque atual (usada para contar)
        $currentStockSubquery = StockMovement::selectRaw('SUM(quantity)')
            ->whereColumn('product_id', 'products.id')
            ->limit(1);

        // O 'lowStock' agora calcula o saldo e filtra o resultado
        $lowStock = Product::query()
            ->addSelect(['current_stock_balance' => $currentStockSubquery])
            ->having('current_stock_balance', '<=', $minimumStock)
            ->having('current_stock_balance', '>', 0)
            ->count(); 
        
        // 4. Média do Ticket
        $avgTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            Stat::make('Receita Total', number_format($totalRevenue, 0, ',', '.') . ' Kz')
                ->description('Vendas completas no período')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Total de Pedidos', number_format($totalOrders, 0, ',', '.'))
                ->description('Pedidos concluídos')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),
                
            Stat::make('Ticket Médio', number_format($avgTicket, 0, ',', '.') . ' Kz')
                ->description('Valor médio por pedido')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),

            Stat::make('Estoque Baixo', number_format($lowStock, 0, ',', '.'))
                ->description('Produtos abaixo do limite de alerta')
                ->descriptionIcon('heroicon-m-archive-box-x-mark')
                ->color('danger'),
        ];
    }
    
    // View customizada para renderizar os cards
    public function render()
    {
        return view('filament-widgets.stats-overview-widget', [
            'stats' => $this->getStats(),
        ]);
    }
}