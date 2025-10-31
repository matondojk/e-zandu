<?php

// app/Http/Controllers/Api/SubscriptionWebhookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'event' => 'required|in:renew,pause,cancel',
            'plan_name' => 'sometimes|string',
            'end_date' => 'sometimes|date',
        ]);

        // Busca a primeira assinatura (a única do sistema)
        $subscription = Subscription::firstOrCreate(['id' => 1], [
            'status' => 'active',
            'start_date' => now()
        ]);

        $dataToUpdate = [
            'plan_name' => $request->input('plan_name', $subscription->plan_name),
            'end_date' => $request->input('end_date', $subscription->end_date),
        ];

        switch ($validated['event']) {
            case 'renew':
                $dataToUpdate['status'] = 'active';
                $dataToUpdate['renewed_at'] = now();
                // Limpa os status anteriores ao renovar
                $dataToUpdate['paused_at'] = null;
                $dataToUpdate['canceled_at'] = null;
                break;
                
            case 'pause':
                $dataToUpdate['status'] = 'paused';
                $dataToUpdate['paused_at'] = now();
                break;
                
            case 'cancel':
                $dataToUpdate['status'] = 'canceled';
                $dataToUpdate['canceled_at'] = now();
                break;
        }

        $subscription->update($dataToUpdate);

        return response()->json(['message' => 'Webhook recebido com sucesso.']);
    }
}