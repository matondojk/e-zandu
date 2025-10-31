<?php

namespace App\Http\Responses;

use App\Models\Subscription;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // <-- ADICIONE ESTE 'USE'

class LoginResponse extends BaseLoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        Log::info('[LoginResponse] Classe de resposta de login personalizada foi acionada.');

        // Pega a primeira (e única) assinatura do sistema
        $subscription = Subscription::first();

        // Se não houver assinatura OU se a assinatura estiver pausada ou cancelada
        if (!$subscription || in_array($subscription->status, ['paused', 'canceled'])) {
            
            Log::warning('[LoginResponse] Tentativa de login bloqueada. Assinatura inativa.', [
                'status' => $subscription ? $subscription->status : 'Nenhuma assinatura encontrada',
                'user_email' => $request->input('email'), // Loga o email que tentou logar
            ]);
            
            // Desloga o usuário que acabou de tentar logar
            Auth::logout();

            // Redireciona de volta para o login com uma mensagem de erro
            return redirect()->route('filament.auth.login')->withErrors([
                'email' => 'O acesso ao sistema está bloqueado. A assinatura está inativa.',
            ]);
        }

        Log::info('[LoginResponse] Login permitido. Assinatura ativa.', [
            'status' => $subscription->status,
            'user_email' => $request->input('email'),
        ]);

        // Se a assinatura estiver 'active', permite o login normalmente
        return parent::toResponse($request);
    }
}