<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OrderService; // Importar serviços
use App\Services\WhatsAppService; // Importar serviços
use App\Models\Customer;
use Illuminate\Support\Facades\Log;
use \Exception;
use App\Http\Controllers\Api\WhatsAppWebhookController; // Importar o Controller

class ProcessWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer;
    protected $messageData;
    protected $messageType;

    /**
     * Tenta 5 vezes antes de falhar
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(Customer $customer, array $messageData, string $messageType)
    {
        $this->customer = $customer;
        $this->messageData = $messageData;
        $this->messageType = $messageType;
    }

    /**
     * Execute the job.
     * * Injetamos os serviços e o controller para usar os métodos que já existem.
     */
    public function handle(
        OrderService $orderService, 
        WhatsAppService $whatsAppService
    ): void
    {
        Log::info("JOB: Iniciando processamento para {$this->customer->phone}");
        
        // Recriamos uma instância do controller com os serviços injetados
        $controller = new WhatsAppWebhookController($orderService, $whatsAppService);
        
        try {
            if ($this->messageType === 'text') {
                // Chamamos a lógica de IA (que era 'private', agora é 'public')
                $controller->processTextMessage($this->customer, $this->messageData['text']['body']);
            
            } elseif ($this->messageType === 'interactive') {
                
                $interactiveData = $this->messageData['interactive'];
                $buttonId = null;

                if ($interactiveData['type'] === 'button_reply') {
                    $buttonId = $interactiveData['button_reply']['id'] ?? '';
                }

                if ($buttonId) {
                    // Chamamos a lógica de botões (que era 'private', agora é 'public')
                    $controller->handleInteractiveMessage($this->customer, $buttonId);
                }
            }

        } catch (Exception $e) {
            Log::error("Erro no Job ProcessWhatsAppMessage: " . $e->getMessage());
            // Lançamos a exceção para que o Job possa tentar novamente (até 5x)
            throw $e;
        }
    }
}