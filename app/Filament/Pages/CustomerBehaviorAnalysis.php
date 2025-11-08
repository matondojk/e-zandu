<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\IntelligenceService;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use App\Services\SubscriptionManager;

class CustomerBehaviorAnalysis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static string $view = 'filament.pages.customer-behavior-analysis';

    // Coloca no grupo de IA
    protected static ?string $navigationGroup = 'Recursos de IA';
    protected static ?string $navigationLabel = 'Análise de Comportamento';
    protected static ?string $title = 'Análise de Comportamento (Cross-Sell)';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'analise-de-comportamento'; // URL em português

    // --- 2. ADICIONAR OS MÉTODOS DE BADGE ---

    /**
     * Mostra um badge "ESSENCIAL" se o utilizador atual (Trial) não tiver acesso.
     */
    public static function getNavigationBadge(): ?string
    {
        $manager = app(SubscriptionManager::class);

        // A feature key é 'analise-inteligente', como definido no Middleware/Manager
        if (!$manager->canUse('analise-inteligente')) {
            return 'ESSENCIAL'; // Retorna o badge para o plano "Trial"
        }

        // Se pode usar (É Essencial ou PRO), não mostra nada
        return null;
    }

    /**
     * Define a cor do badge (usamos 'warning' - laranja - para destaque).
     */
    public static function getNavigationBadgeColor(): ?string
    {
        if (!app(SubscriptionManager::class)->canUse('analise-inteligente')) {
            return 'warning';
        }
        
        return null;
    }

    // --- FIM DA ADIÇÃO ---

    // Estado da Página
    public ?string $analysisResult = null;
    public bool $isLoading = false;

    protected IntelligenceService $ai;

    public function boot(IntelligenceService $ai): void
    {
        $this->ai = $ai;
    }

    public function getTitle(): string | Htmlable
    {
        return static::$title;
    }

    /**
     * Ação principal: Chamada pelo botão na view
     */
    public function runAnalysis(): void
    {
        $this->isLoading = true;
        $this->analysisResult = null;

        try {
            // Chama a nova função 6 que criámos no serviço
            $this->analysisResult = $this->ai->generateCrossSellAnalysis();
        } catch (Exception $e) {
            Notification::make()
                ->title('Erro ao Gerar Análise')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->analysisResult = "Ocorreu um erro: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }
}
