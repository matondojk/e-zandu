<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubscriptionManager;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CheckTenantSubscription
{
    /**
     * Mapeia o início da rota (path) para a "feature key" que definimos no Manager.
     * * NÃO PRECISAMOS de listar as features CORE aqui,
     * pois o "Portão 2" (isActive) já as protege.
     */
    protected $featureMap = [
        // --- Features PRO ---
        'admin/whatsapp' => 'whatsapp',
        'admin/campaigns' => 'campaigns',

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
        // 1. Se não estiver logado, deixa passar (o middleware 'auth' trata disso)
        if (!Auth::check()) {
            return $next($request);
        }

        // --- 2. O "PORTÃO" PRINCIPAL (Assinatura Ativa) ---
        // Se a assinatura não estiver ativa (ex: 'canceled', 'paused', ou 'trial' expirado)
        if (!$this->subscription->isActive()) {
            
            // Exceção: Permite a passagem para a rota de gestão de assinatura
            if ($request->is('admin/assinatura*')) { // Slug em PT
                return $next($request); 
            }
            
            // Lança 403 para todas as outras rotas
            throw new AccessDeniedHttpException('A sua assinatura está inativa ou expirou. Por favor, aceda à página de Assinaturas para regularizar a situação.');
        }

        // --- 3. O "PORTÃO" DE FUNCIONALIDADES (Essencial e PRO) ---
        
        $requiredFeature = null;

        // Verifica se a rota atual está no nosso mapa de features
        foreach ($this->featureMap as $path => $featureKey) {
            if ($request->is($path) || $request->is($path . '/*')) {
                $requiredFeature = $featureKey;
                break;
            }
        }

        // Se a rota requer uma funcionalidade (ex: 'whatsapp' ou 'chat-de-analise')
        if ($requiredFeature) {
            
            // Verifica se o plano do cliente (Trial, Essencial, Pro) pode usar
            if (!$this->subscription->canUse($requiredFeature)) { 
                
                // Se não pode, dá uma mensagem de erro específica
                $plan = $this->subscription->getPlanIdentifier(); // Pega o plano atual
                $message = 'Este é um recurso que requer um plano superior.';

                if ($plan === 'trial') {
                    $message = 'Este é um recurso do Plano Essencial ou PRO. O seu período de Teste (Trial) não inclui esta funcionalidade. Faça upgrade!';
                }
                if ($plan === 'essencial') {
                    $message = 'Este é um recurso exclusivo do Plano PRO. Por favor, faça upgrade da sua conta.';
                }

                throw new AccessDeniedHttpException($message);
            }
        }

        // Se passou em todos os portões:
        // 1. Assinatura está ativa (Portão 2)
        // 2. E a rota é CORE (não está no $featureMap) OU o plano tem permissão (Portão 3)
        return $next($request);
    }
}