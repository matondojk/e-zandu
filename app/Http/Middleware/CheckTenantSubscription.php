<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionManager;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification; // Importante: Importar a notificação

class CheckTenantSubscription
{
    /**
     * Mapeia o início da rota (path) para a "feature key".
     */
    protected $featureMap = [
        // --- Features PRO ---
        'admin/whatsapp' => 'whatsapp',
        'admin/campanhas' => 'campaigns',

        // --- Features ESSENCIAL (IA/BI) ---
        'admin/assistente-de-insights' => 'assistente-de-insights',
        'admin/chat-de-analise' => 'chat-de-analise',
        'admin/analise-de-comportamento' => 'analise-de-comportamento',
        'admin/relatorios-guardados' => 'relatorios-guardados',
        'admin/analise-inteligente' => 'analise-inteligente',
    ];

    public function __construct(protected SubscriptionManager $subscription)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        // 1. Se não estiver logado, deixa passar (auth middleware trata)
        if (!Auth::check()) {
            return $next($request);
        }

        // --- PREVENÇÃO DE LOOP DE REDIRECIONAMENTO ---
        // Se o utilizador já está a tentar aceder à página de assinaturas, deixamos passar.
        // Verificamos pelo nome da rota (mais seguro) ou pelo URL.
        if ($request->routeIs('filament.admin.pages.assinaturas') || $request->is('admin/assinaturas*')) {
            return $next($request);
        }

        // --- 2. O "PORTÃO" PRINCIPAL (Assinatura Ativa) ---
        if (!$this->subscription->isActive()) {
            
            Notification::make()
                ->title('Acesso Bloqueado')
                ->body('A sua assinatura está inativa ou expirou. Por favor, renove para continuar a usar o sistema.')
                ->danger() // Vermelho para indicar urgência
                ->persistent() // O utilizador tem de fechar manualmente
                ->send();

            // Redireciona para a página criada no passo anterior
            return redirect()->route('filament.admin.pages.assinaturas');
        }

        // --- 3. O "PORTÃO" DE FUNCIONALIDADES (Essencial e PRO) ---
        
        $requiredFeature = null;

        // Verifica se a rota atual está no mapa
        foreach ($this->featureMap as $path => $featureKey) {
            if ($request->is($path) || $request->is($path . '/*')) {
                $requiredFeature = $featureKey;
                break;
            }
        }

        if ($requiredFeature) {
            // Verifica se o plano permite
            if (!$this->subscription->canUse($requiredFeature)) { 
                
                $plan = $this->subscription->getPlanIdentifier();
                $message = 'Este recurso requer um upgrade de plano.';

                if ($plan === 'trial') {
                    $message = 'Funcionalidade não disponível no período de Teste. Faça o upgrade para Essencial ou PRO.';
                }
                elseif ($plan === 'essencial') {
                    $message = 'Esta funcionalidade é exclusiva do Plano PRO. Faça o upgrade para aceder.';
                }

                Notification::make()
                    ->title('Upgrade Necessário')
                    ->body($message)
                    ->warning() // Amarelo/Laranja para indicar restrição
                    ->persistent()
                    ->send();

                return redirect()->route('filament.admin.pages.assinaturas');
            }
        }

        return $next($request);
    }
}