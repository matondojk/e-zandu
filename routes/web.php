<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportViewController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return response('success');
});

Route::group([
    'middleware' => [
        'auth:sanctum',
        config('filament.auth.middleware.Panel'), // Middleware de autenticação do Filament
    ],
    'prefix' => 'admin', // O seu prefixo do Filament
], function () {

    // Rota para o Iframe do Relatório Inteligente
    Route::get('reports/view', [ReportViewController::class, 'show'])
         ->name('reports.view');

    // ... (o Filament cuida das outras rotas)
});

Route::get('pagamento/{uuid}', [PaymentController::class, 'show'])->name('payment.show');
Route::post('pagamento/{uuid}', [PaymentController::class, 'store'])->name('payment.store');
