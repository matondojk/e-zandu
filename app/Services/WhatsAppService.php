<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use \Exception;

// --- Imports Adicionados para Notificações do Painel ---
use App\Models\User; // Para encontrar os admins
use App\Notifications\AdminPanelNotification; // A nossa nova classe de notificação
use Illuminate\Support\Facades\Notification; // Fachada de Notificação
// --- Fim dos Imports ---

class WhatsAppService
{
    private $token;
    private $phoneId;
    // private $adminPhone; // Removido, pois vamos buscá-lo do Cache/Settings

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneId = config('services.whatsapp.phone_id'); // Mantido o seu phoneId
        // $this->adminPhone = config('services.whatsapp.admin_phone'); // Removido
    }

    /**
     * Envia uma mensagem de TEXTO simples.
     */
    public function sendTextMessage(string $body, string $recipientPhone)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'text',
            'text' => ['body' => $body]
        ];
        $this->sendApiRequest($payload, "TEXTO");
    }

    /**
     * Envia uma mensagem de DOCUMENTO (PDF).
     */
    public function sendDocumentMessage(string $documentUrl, string $recipientPhone, string $filename)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'document',
            'document' => [
                'link' => $documentUrl,
                'filename' => $filename
            ]
        ];
        $this->sendApiRequest($payload, "DOCUMENTO");
    }

    /**
     * ✅ NOVO MÉTODO ADICIONADO
     * Envia uma mensagem de IMAGEM (usando URL).
     */
    public function sendImageMessage(string $imageUrl, string $recipientPhone, ?string $caption = null)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'image',
            'image' => [
                'link' => $imageUrl
            ]
        ];

        // Adiciona legenda (caption) se for fornecida
        if ($caption) {
            $payload['image']['caption'] = $caption;
        }

        $this->sendApiRequest($payload, "IMAGEM");
    }


    /**
     * Função genérica para enviar MENSAGEM INTERATIVA (Botões)
     */
    public function sendInteractiveMessage(string $body, string $recipientPhone, ?string $footer, ?string $imageUrl, array $buttons)
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $recipientPhone,
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => ['text' => $body],
                'action' => ['buttons' => []]
            ]
        ];

        if ($imageUrl) {
            $payload['interactive']['header'] = [
                'type' => 'image',
                'image' => ['link' => $imageUrl]
            ];
        }
        if ($footer) {
            $payload['interactive']['footer'] = ['text' => $footer];
        }
        foreach ($buttons as $button) {
            $payload['interactive']['action']['buttons'][] = [
                'type' => 'reply',
                'reply' => ['id' => $button['id'], 'title' => $button['title']]
            ];
        }

        $this->sendApiRequest($payload, "INTERATIVA");
    }

    
    // --- MÉTODO notifyAdmin CORRIGIDO ---
    /**
     * Notifica o Admin (via WhatsApp E Painel Filament)
     *
     * @param string $whatsappMessage A mensagem para o WhatsApp.
     * @param string|null $panelMessage A mensagem para o Painel (pode ser a mesma).
     * @param string|null $panelUrl A URL para onde a notificação do painel deve levar.
     * @param string $icon O ícone do Heroicon para o painel.
     */
    public function notifyAdmin(
        string $whatsappMessage, 
        ?string $panelMessage = null,
        ?string $panelUrl = null,
        string $icon = 'heroicon-o-bell'
    ) {
        // --- 1. Enviar Notificação WhatsApp (Como antes) ---
        $adminPhone = Cache::remember('shop_settings', 60*5, function () {
            // Assegura que Setting::first() não é nulo antes de aceder a admin_phone
            return Setting::first(); 
        })->admin_phone ?? null; // Usa ?? null para segurança

        if (empty($adminPhone)) {
            Log::warning('Número do Admin (admin_phone) não configurado. Notificação WhatsApp não enviada.');
        } else {
            // Usa $whatsappMessage (o primeiro argumento)
            $this->sendTextMessage($whatsappMessage, $adminPhone);
        }

        // --- 2. Enviar Notificação ao Painel Filament ---
        try {
            // Se nenhuma mensagem de painel for fornecida, usa a do WhatsApp
            $panelMessage = $panelMessage ?? $whatsappMessage;
            
            // Se nenhuma URL for fornecida, aponta para o dashboard
            $panelUrl = $panelUrl ?? '/admin'; 

            $adminUsers = User::all(); // Pega TODOS os utilizadores do painel

            if ($adminUsers->isEmpty()) {
                Log::warning('Nenhum utilizador encontrado para notificar no painel Filament.');
                return; // Retorna aqui para evitar erro, mas o WhatsApp pode ter sido enviado
            }
            
            // Envia a nossa notificação customizada para todos os admins
            Notification::send($adminUsers, new AdminPanelNotification($panelMessage, $panelUrl, $icon));

        } catch (\Exception $e) {
            Log::error('Falha ao enviar notificação para o painel Filament: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }
    // --- FIM DO MÉTODO CORRIGIDO ---


    /**
     * Função Central de Envio de API (Mantida a sua lógica)
     */
    private function sendApiRequest(array $payload, string $messageType)
    {
        if (!$this->token || !$this->phoneId) {
            Log::error('WHATSAPP_API_TOKEN ou WHATSAPP_PHONE_ID não estão configurados!');
            return;
        }

        $url = "https://graph.facebook.com/v19.0/{$this->phoneId}/messages";

        try {
            $response = Http::withToken($this->token)->post($url, $payload);
            if ($response->successful()) {
                Log::info("Mensagem de [{$messageType}] enviada para {$payload['to']}.");
            } else {
                Log::error("Falha ao enviar mensagem de [{$messageType}] para {$payload['to']}: " . $response->body());
            }
        } catch (Exception $e) {
            Log::error("Exceção ao enviar mensagem de [{$messageType}]: " . $e->getMessage());
        }
    }
}