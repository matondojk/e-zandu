<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Garante que só estamos a verificar administradores (o Filament faz a autenticação)
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            return $next($request);
        }

        // Tenta obter o primeiro (e único) registo de assinatura
        $subscription = Subscription::first();

        // 2. Regra de Bloqueio
        if (!$subscription || in_array($subscription->status, ['paused', 'canceled'])) {
            
            // Log para rastreabilidade
            Log::warning('[Subscription Middleware] Tentativa de acesso bloqueada.', [
                'status' => $subscription ? $subscription->status : 'não encontrada',
                'user_id' => Auth::id(),
            ]);

            // ✅ Solução mais limpa: Bloquear com 403 Forbidden. 
            // O Filament irá capturar este erro e mostrar uma página de erro limpa.
            $status = $subscription ? $subscription->status : 'inativa';
            return abort(403, "O acesso ao painel de administração está bloqueado. A assinatura ({$status}) está inativa. Por favor, contacte o suporte.");
        }

        // 3. Assinatura ativa ou ativa/trial — Permite a passagem
        return $next($request);
    }
}