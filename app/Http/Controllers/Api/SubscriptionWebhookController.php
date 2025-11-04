<?php

// app/Http/Controllers/Api/SubscriptionWebhookController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon; // Importar Carbon

class SubscriptionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            // ADICIONADO 'active' À VALIDAÇÃO
            'event' => 'required|in:active,renew,pause,cancel',
            'plan_name' => 'sometimes|string',
            'end_date' => 'sometimes|date', // A nova end_date é opcional no request
        ]);

        $subscription = Subscription::firstOrCreate(['id' => 1], [
            'status' => 'active',
            'start_date' => now(),
            'end_date' => now()->addYear(), // Define uma end_date inicial (será sobreposta se o evento for 'active')
            'plan_name' => 'Plano Padrão'
        ]);

        // Inicializa o array de dados a atualizar
        $dataToUpdate = [
            'plan_name' => $request->input('plan_name', $subscription->plan_name),
        ];

        switch ($validated['event']) {
            
            // --- NOVO EVENTO 'active' ---
            // Usado para iniciar ou re-iniciar a subscrição com um período de 1 mês
            case 'active':
                $dataToUpdate['status'] = 'active';
                $dataToUpdate['start_date'] = now(); // Define o início para agora
                $dataToUpdate['end_date'] = now()->addMonth(); // Define a expiração para daqui a 1 MÊS
                // Limpa todos os outros status para um começo limpo
                $dataToUpdate['paused_at'] = null;
                $dataToUpdate['canceled_at'] = null;
                $dataToUpdate['renewed_at'] = null;
                break;

            case 'renew':
                $dataToUpdate['status'] = 'active';
                $dataToUpdate['renewed_at'] = now();
                $dataToUpdate['paused_at'] = null;
                $dataToUpdate['canceled_at'] = null;

                // Se a plataforma de pagamento (webhook) nos enviar a nova data final, usamos essa.
                if ($request->has('end_date')) {
                    $dataToUpdate['end_date'] = $request->input('end_date');
                } else {
                    // Se o webhook não enviar, calculamos a nova data (assumindo 1 ano de RENOVAÇÃO)
                    $currentEndDate = Carbon::parse($subscription->end_date);
                    
                    // Se a data atual já passou, começa a contar a partir de HOJE
                    if ($currentEndDate->isPast()) {
                        $dataToUpdate['end_date'] = now()->addYear();
                    } else {
                        // Se ainda não expirou (renovação antecipada), adiciona 1 ano à data existente
                        $dataToUpdate['end_date'] = $currentEndDate->addYear();
                    }
                }
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