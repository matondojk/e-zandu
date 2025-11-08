<?php

namespace App\Services;

use App\Models\SubscriptionDetail;
use Illuminate\Support\Facades\Cache;

class SubscriptionManager
{
    protected ?SubscriptionDetail $subscription;
    protected ?string $planIdentifier;

    public function __construct()
    {
        // Pega a subscrição do cache ou da BD
        $this->subscription = Cache::rememberForever(
            'tenant_subscription_details',
            fn() => SubscriptionDetail::first()
        );

        $this->planIdentifier = $this->subscription?->plan_identifier;
    }

    /**
     * Verifica se a assinatura está PAGA/GRÁTIS e DENTRO DO PRAZO.
     */
    public function isActive(): bool
    {
        if (!$this->subscription) {
            return false; // Não tem assinatura
        }

        // Está 'active' E a data de expiração (ends_at) ainda não chegou.
        return $this->subscription->status === 'active' &&
            $this->subscription->ends_at?->isFuture();
    }

    /**
     * Retorna o 'identifier' do plano (ex: 'trial', 'essencial', 'pro')
     * Só retorna o plano se a assinatura estiver ativa.
     */
    public function getPlanIdentifier(): ?string
    {
        if (!$this->isActive()) {
            return null; // Se não está ativa, não tem plano
        }
        return $this->planIdentifier;
    }

    /**
     * Verifica se o plano atual pode usar uma funcionalidade específica.
     */
    public function canUse(string $featureKey): bool
    {
        $plan = $this->getPlanIdentifier();

        if (!$plan) {
            return false; // Assinatura inativa
        }

        // Lista de features
        $features = $this->getFeaturesForPlan($plan);

        return in_array($featureKey, $features);
    }

    /**
     * Define a lógica de HERANÇA de planos.
     * Esta é a função mais importante.
     */
    public static function getFeaturesForPlan(string $plan): array
    {
        // 1. Recursos CORE (Incluídos no TRIAL)
        $coreFeatures = [
            'analytics-dashboard',
            'clientes',
            'vendas',
            'coupons',
            'avaliacoes',
            'taxas-de-frete',
            'produtos',
            'categorias',
            'fornecedores',
            'armazens',
            'compras',
            'assinatura',
            'despesas-operacionais',
            'metodos-de-pagamento',
            'administradores',
            'configuracoes-da-loja',
            'logs',
            'personalizacao'
        ];

        // 2. Recursos ESSENCIAL (IA/BI)
        $essencialFeatures = [
            'assistente-de-insights',
            'chat-de-analise',
            'analise-de-comportamento',
            'relatorios-guardados',
            'analise-inteligente'
        ];

        // 3. Recursos PRO (Marketing)
        $proFeatures = [
            'whatsapp',
            'campaigns'
        ];

        // 4. Lógica de Herança
        switch ($plan) {
            // PRO tem TUDO
            case 'pro':
                return array_merge($coreFeatures, $essencialFeatures, $proFeatures);

                // Essencial tem CORE + ESSENCIAL
            case 'essencial':
                return array_merge($coreFeatures, $essencialFeatures);

                // Trial tem apenas CORE
            case 'trial':
                return $coreFeatures;

            default:
                return [];
        }
    }

    /**
     * Limpa o cache quando o Webhook atualiza a assinatura.
     */
    public static function clearCache(): void
    {
        Cache::forget('tenant_subscription_details');
    }
}
