<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\IntelligenceService;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable; // Necessário para o título

class BusinessInsights extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string $view = 'filament.pages.business-insights';

    // ✅ NOVO: Coloca no grupo de IA
    protected static ?string $navigationGroup = 'Recursos de IA';
    protected static ?string $navigationLabel = 'Assistente de Insights';
    protected static ?string $title = 'Assistente de Insights de Negócio';
    protected static ?int $navigationSort = 1; // Coloca no topo do grupo

    protected static ?string $slug = 'assistente-de-insights';

    // Estado da Página
    public ?string $insightsResult = null;
    public bool $isLoading = false;

    protected IntelligenceService $ai;

    // Injeta o serviço de IA no arranque
    public function boot(IntelligenceService $ai): void
    {
        $this->ai = $ai;
    }

    // Título da Página
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
        $this->insightsResult = null; // Limpa resultados antigos

        try {
            // Chama a nova função 3 que criámos no serviço
            $this->insightsResult = $this->ai->generateBusinessInsights();
        } catch (Exception $e) {
            Notification::make()
                ->title('Erro ao Gerar Insights')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->insightsResult = "Ocorreu um erro: " . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }
}
