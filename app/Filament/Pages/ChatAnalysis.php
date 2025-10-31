<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Services\IntelligenceService;
use Exception;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms;

class ChatAnalysis extends Page implements HasForms
{
    use InteractsWithForms; // Necessário para o formulário de chat

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string $view = 'filament.pages.chat-analysis';

    // Coloca no grupo de IA
    protected static ?string $navigationGroup = 'Recursos de IA';
    protected static ?string $navigationLabel = 'Chat de Análise';
    protected static ?string $title = 'Chat de Análise (IA Conversacional)';
    protected static ?int $navigationSort = 2; // Coloca abaixo do Assistente

    // --- Estado do Chat ---
    public string $currentMessage = ''; // O que o utilizador está a digitar
    public array $chatHistory = []; // Armazena todo o histórico
    public bool $isLoading = false;

    protected IntelligenceService $ai;

    public function boot(IntelligenceService $ai): void
    {
        $this->ai = $ai;
    }

    public function mount(): void
    {
        // Mensagem inicial de boas-vindas
        $this->chatHistory[] = [
            'role' => 'ai',
            'analysis' => 'Olá! Eu sou o seu assistente de chat. Faça-me uma pergunta sobre os seus dados (ex: "Qual foi o total de vendas ontem?" ou "Liste os 5 produtos mais caros").',
            'data' => [],
            'columns' => [],
            'sql_query' => null
        ];
    }

    /**
     * Define o formulário (apenas a caixa de texto)
     */
    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('currentMessage')
                ->label('Faça uma pergunta')
                ->placeholder('Ex: Quanto vendemos no mês passado?')
                ->required()
                ->autocomplete(false), // Desliga o autocomplete do navegador
        ];
    }

    /**
     * Ação principal: Enviar Mensagem
     */
    public function sendMessage(): void
    {
        $this->validate(); // Valida o 'currentMessage'
        
        $this->isLoading = true;
        $userQuestion = $this->currentMessage;

        // 1. Adiciona a pergunta do utilizador ao histórico
        $this->chatHistory[] = [
            'role' => 'user',
            'analysis' => $userQuestion,
        ];
        
        $this->currentMessage = ''; // 2. Limpa a caixa de texto imediatamente

        // 3. Envia para o "cérebro" e obtém a resposta completa
        try {
            $response = $this->ai->runChatQuery($userQuestion);
            $response['role'] = 'ai';
            
            // Adiciona a resposta da IA ao histórico
            $this->chatHistory[] = $response;

        } catch (Exception $e) {
            $this->chatHistory[] = [
                'role' => 'ai',
                'analysis' => 'Ocorreu um erro crítico ao contactar o serviço de IA: ' . $e->getMessage(),
                'data' => [],
                'columns' => [],
                'sql_query' => null
            ];
        } finally {
            $this->isLoading = false;
        }
    }
}