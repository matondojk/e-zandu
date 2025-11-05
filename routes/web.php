<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportViewController;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\PrivacyPolicyPage;
use App\Livewire\Pages\ProductDetailPage;

// ✅ CORREÇÃO: Importar do namespace correto do Livewire 3
use App\Livewire\SetupWizard; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::post('payment/{uuid}/apply-coupon', [PaymentController::class, 'applyCoupon'])->name('payment.applyCoupon');
Route::get('/politica-de-privacidade', PrivacyPolicyPage::class)->name('privacy.policy');
Route::get('/', HomePage::class);
Route::get('/produto/{slug}', ProductDetailPage::class)->name('product.detail');
Route::group([
    'middleware' => [
        'auth:sanctum',
        config('filament.auth.middleware.Panel'), // Middleware de autenticação do Filament
    ],
    'prefix' => 'admin',
], function () {

    Route::get('reports/view', [ReportViewController::class, 'show'])
        ->name('reports.view');
});

Route::get('pagamento/{uuid}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('pagamento/{uuid}', [PaymentController::class, 'store'])->name('payment.store');

Route::get('/setup', SetupWizard::class)
    ->name('setup.index');

// Route::get('/login', function () {
//     return redirect()->route('filament.admin.auth.login');
// })->name('login');