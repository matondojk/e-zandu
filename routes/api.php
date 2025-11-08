<?php

use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('whatsapp-webhook', [WhatsAppWebhookController::class, 'verify']);
Route::post('whatsapp-webhook', [WhatsAppWebhookController::class, 'handle']);
Route::post('/webhook/subscription', [WebhookController::class, 'handleSubscription']);
