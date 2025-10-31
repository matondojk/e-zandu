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

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
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
    }
}
