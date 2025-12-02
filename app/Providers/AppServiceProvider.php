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
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Setting;

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
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {

            $setting = Setting::find('00000000-0000-0000-0000-000000000001') ?? Setting::first();

            $storeName = $setting->store_name ?? config('app.name');

            return (new MailMessage)
                ->subject('Redefinição de Senha | ' . $storeName)
                ->greeting('Olá!')
                ->line('Você está recebendo este e-mail porque recebemos um pedido de redefinição de senha para sua conta.')
                ->action('Redefinir Minha Senha', url(route('password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false)))
                ->line('Este link de redefinição de senha expira em 60 minutos.')
                ->line('Se você não solicitou uma redefinição de senha, nenhuma ação adicional é necessária.')
                ->salutation('Atenciosamente, Equipe ' . $storeName);
        });

        if (config('app.demo_mode')) {
            DB::beginTransaction();

            app()->terminating(function () {
                DB::rollBack();
            });
        }

        if ($this->app->runningInConsole()) {
            return;
        }

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
