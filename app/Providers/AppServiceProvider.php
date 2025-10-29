<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Cart;        
use App\Observers\CartObserver;
use App\Models\Stock;         
use App\Observers\StockObserver; 
use App\Models\ProductImage; 
use App\Observers\ProductImageObserver; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
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
