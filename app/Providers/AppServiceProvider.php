<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cart;        
use App\Observers\CartObserver;
use App\Models\Stock;         
use App\Observers\StockObserver; 
use App\Models\ProductImage; 
use App\Observers\ProductImageObserver; 
use Filament\Facades\Filament;
use App\Http\Responses\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract; 
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Cart::observe(CartObserver::class);
        Stock::observe(StockObserver::class);
        ProductImage::observe(ProductImageObserver::class);

        Livewire::component('sales-stats-overview-widget', \App\Livewire\SalesStatsOverviewWidget::class);
        Livewire::component('sales-chart-widget', \App\Filament\Widgets\SalesChartWidget::class);
        Livewire::component('latest-orders-table-widget', \App\Filament\Widgets\LatestOrdersTableWidget::class);
        Livewire::component('low-stock-table-widget', \App\Filament\Widgets\LowStockTableWidget::class);
        Livewire::component('purchases-chart-widget', \App\Filament\Widgets\PurchasesChartWidget::class);
    }
}
