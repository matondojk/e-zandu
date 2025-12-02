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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        if (config('app.demo_mode')) {
            DB::beginTransaction();

            app()->terminating(function () {
                DB::rollBack();
            });
        }

        if ($this->app->runningInConsole()) {
            return;
        }

        // try {
        //     if (Schema::hasTable('whatsapp_settings')) {
        //         $settings = WhatsAppSetting::first();

        //         if ($settings) {

        //             // --- 2. A CORREÇÃO ---
        //             // Remova as aspas extra daqui
        //             config([
        //                 'services.whatsapp.token'       => $settings->token, // <-- CORRIGIDO
        //                 'services.whatsapp.phone_id'    => $settings->phone_id,
        //                 'services.whatsapp.verify_token' => $settings->verify_token,
        //                 'services.whatsapp.admin_phone' => $settings->admin_phone,
        //             ]);

        //             // --- 3. O LOG ---
        //             // Log para confirmar que foi carregado.
        //             // Não logue o token inteiro por segurança.
        //             Log::info('Configurações do WhatsApp carregadas da BD com sucesso.');
        //             Log::debug('Phone ID do WhatsApp: ' . $settings->phone_id);
        //             // Logar apenas os primeiros e últimos caracteres do token é uma boa prática
        //             if ($settings->token) {
        //                 Log::debug('Token do WhatsApp (parcial): ' . $settings->token . substr($settings->token, -5));
        //             }
        //         } else {
        //             // Adiciona um log caso a tabela esteja vazia
        //             Log::warning('Tabela whatsapp_settings está vazia. Usando valores do .env (se existirem).');
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
