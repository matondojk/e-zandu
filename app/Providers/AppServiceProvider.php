<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cart;
use App\Observers\CartObserver;
use App\Models\Stock;
use App\Observers\StockObserver;
use App\Models\ProductImage;
use App\Observers\ProductImageObserver;
use Livewire\Livewire;
use App\Models\WhatsAppSetting;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            return;
        }

        // try {
        //     if (Schema::hasTable('whatsapp_settings')) {
        //         $settings = WhatsAppSetting::first();

        //         if ($settings) {
        //             config([
        //                 'services.whatsapp.token' => $settings->token,
        //                 'services.whatsapp.phone_id' => $settings->phone_id,
        //                 'services.whatsapp.verify_token' => $settings->verify_token,
        //                 'services.whatsapp.admin_phone' => $settings->admin_phone,
        //             ]);
        //         }
        //     }
        // } catch (\Exception $e) {
        //     Log::error('Falha ao carregar configurações do WhatsApp da BD: ' . $e->getMessage());
        // }

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
