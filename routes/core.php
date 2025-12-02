<?php

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