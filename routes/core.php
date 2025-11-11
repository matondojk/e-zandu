<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Principais (Core) da Aplicação
|--------------------------------------------------------------------------
|
| Este ficheiro contém as rotas web principais. É carregado pelo
| ficheiro principal 'routes/web.php' para manter a organização.
|
*/

// --- 1. Rotas Públicas (Frontend) ---
// (Rota do produto removida)


// --- 2. Rotas de Pagamento (Gateway) ---
// Rotas relacionadas com o processo de checkout e pagamento.
Route::name('payment.')->group(function () {
    Route::get('pagamento/{uuid}', [PaymentController::class, 'show'])->name('show');
    Route::post('pagamento/{uuid}', [PaymentController::class, 'store'])->name('store');
    Route::post('payment/{uuid}/apply-coupon', [PaymentController::class, 'applyCoupon'])->name('applyCoupon');
});


// --- 3. Rotas de Admin (Protegidas) ---
// Rotas acedidas dentro do painel de admin, protegidas por autenticação.
Route::group([
    'middleware' => [
        'auth:sanctum',
        config('filament.auth.middleware.Panel'), // Middleware de autenticação do Filament
    ],
    'prefix' => 'admin',
    'as' => 'admin.', // Prefixo de nome para evitar conflitos (ex: admin.reports.view)
], function () {

    Route::get('reports/view', [ReportViewController::class, 'show'])
        ->name('reports.view');
});


// --- 4. Rotas de Subscrição (Aviso) ---
// Rotas para exibir informações de subscrição.
Route::get('/subscription/notice', function () {
    $sub = \App\Models\SubscriptionDetail::first();
    return view('admin.subscription_notice', ['subscription' => $sub]);
})->name('admin.subscription.notice');

Route::get('/subscription/history', function () {
    $sub = \App\Models\SubscriptionDetail::first();
    return view('admin.subscription_notice', ['subscription' => $sub]);
})->name('admin.subscription.history');