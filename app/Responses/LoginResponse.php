<?php

namespace App\Http\Responses;

use App\Models\Subscription;
use Filament\Http\Responses\Auth\LoginResponse as BaseLoginResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends BaseLoginResponse
{
    /**
     * Handle the response after a successful or blocked login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Livewire\Features\SupportRedirects\Redirector
     */
    public function toResponse($request)
    {
        Log::info('[LoginResponse] Classe de resposta de login personalizada acionada.', [
            'user_email' => $request->input('email'),
        ]);

        // Obtém a assinatura (ajuste conforme sua lógica)
        $subscription = Subscription::first();

        // Verifica se não há assinatura ou se está inativa
        if (! $subscription || in_array($subscription->status, ['paused', 'canceled'])) {
            Log::warning('[LoginResponse] Tentativa de login bloqueada — assinatura inativa.', [
                'status' => $subscription ? $subscription->status : 'nenhuma assinatura encontrada',
                'user_email' => $request->input('email'),
            ]);

            // Desloga o usuário
            Auth::logout();

            // Retorna redirect compatível com Livewire e Laravel
            return redirect()
                ->route('filament.auth.login')
                ->withErrors([
                    'email' => 'O acesso ao sistema está bloqueado. A assinatura está inativa.',
                ]);
        }

        Log::info('[LoginResponse] Login permitido — assinatura ativa.', [
            'status' => $subscription->status,
            'user_email' => $request->input('email'),
        ]);

        // Continua com o fluxo padrão do Filament
        return parent::toResponse($request);
    }
}
