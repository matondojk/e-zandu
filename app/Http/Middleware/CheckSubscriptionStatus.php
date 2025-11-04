<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon; // Importar Carbon

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Garante que é um admin (o Filament trata da autenticação)
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return $next($request);
        }

        $subscription = Subscription::first();

        // 2. Regra de Bloqueio: Nenhuma assinatura
        if (!$subscription) {
            Log::warning('[Subscription Middleware] Acesso bloqueado (Nenhuma assinatura encontrada).');
            return abort(403, "O acesso ao painel de administração está bloqueado. Nenhuma assinatura foi encontrada.");
        }

        // 3. Regra de Bloqueio: Status inválido
        if (in_array($subscription->status, ['paused', 'canceled'])) {
            Log::warning('[Subscription Middleware] Acesso bloqueado (Status inválido).', [
                'status' => $subscription->status,
            ]);
            return abort(403, "O acesso ao painel está bloqueado. A assinatura encontra-se {$subscription->status}.");
        }

        // --- INÍCIO DA NOVA LÓGICA DE DATA ---

        // 4. Regra de Bloqueio: Expirada E Não Renovada
        $endDate = Carbon::parse($subscription->end_date);
        $renewedAt = $subscription->renewed_at ? Carbon::parse($subscription->renewed_at) : null;

        $isExpired = $endDate->isPast();

        // Verifica se a assinatura NÃO foi renovada recentemente.
        // Considera-se "não renovada" se:
        // 1. O campo renewed_at for NULO
        // 2. OU a data de renovação ($renewedAt) for ANTERIOR à data de expiração ($endDate).
        //    (Isto significa que a renovação que consta na BD é de um ciclo anterior)
        $notRenewedRecently = !$renewedAt || $renewedAt->isBefore($endDate);


        if ($isExpired && $notRenewedRecently) {
            Log::warning('[Subscription Middleware] Acesso bloqueado (Assinatura expirada e não renovada).', [
                'status' => $subscription->status,
                'end_date' => $subscription->end_date,
                'renewed_at' => $subscription->renewed_at,
            ]);

            return abort(403, "Acesso bloqueado. A assinatura expirou em " . $endDate->format('d/m/Y') . " e não foi renovada.");
        }

        // --- FIM DA NOVA LÓGICA DE DATA ---

        // 5. Assinatura Válida
        // Se chegou aqui, está (active) E (dentro da validade OU foi renovada após a expiração)
        return $next($request);
    }
}