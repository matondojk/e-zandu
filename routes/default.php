<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Site\Defaults\Home;
use App\Livewire\Site\Defaults\Shop;
use App\Livewire\Site\Defaults\ProductDetails;
use App\Livewire\Site\Defaults\Cart;
use App\Livewire\Site\Defaults\About;
use App\Livewire\Site\Defaults\Checkout;
use App\Livewire\Site\Defaults\PaymentUpload;
use App\Livewire\Site\Defaults\OrderSuccess;
use App\Livewire\Site\Defaults\Contact;
use App\Livewire\Site\Defaults\Auth\Login;
use App\Livewire\Site\Defaults\MyOrders;
use App\Livewire\Site\Defaults\OrderDetails;
use App\Livewire\Site\Defaults\MyAccount;
use App\Livewire\Site\Checkout\WhatsappCheckout;
use App\Livewire\Site\Order\SiteOrderSuccess;
/*
|--------------------------------------------------------------------------
| Rotas Públicas (Qualquer um acessa)
|--------------------------------------------------------------------------
*/

Route::get('/', Home::class)->name('home');
Route::get('/loja', Shop::class)->name('shop');
Route::get('/produto/{slug}', ProductDetails::class)->name('product.details');
Route::get('/carrinho', Cart::class)->name('cart');
Route::get('/sobre', About::class)->name('about');
Route::get('/contato', Contact::class)->name('contact');
Route::get('/pagamento/{uuid}', WhatsappCheckout::class)->name('payment.show');
Route::get('/whatsapp/sucesso/{uuid}', SiteOrderSuccess::class)->name('whatsapp.success'); 
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Requer Login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Processo de Compra
    Route::get('/checkout', Checkout::class)->name('checkout');
    Route::get('/site/pagamento/{cartId}', PaymentUpload::class)->name('payment.upload');
    Route::get('/sucesso/{cartId}', OrderSuccess::class)->name('order.success');

    // Área do Cliente
    Route::get('/minha-conta', MyAccount::class)->name('my-account'); // Meus Dados
    Route::get('/minha-conta/pedidos', MyOrders::class)->name('my.orders'); // Meus Pedidos
    Route::get('/minha-conta/pedido/{orderId}', OrderDetails::class)->name('order.details'); // Detalhes

    // Logout (Mudei para POST para casar com o formulário do Header)
    Route::post('/logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('home');
    })->name('logout');
});
