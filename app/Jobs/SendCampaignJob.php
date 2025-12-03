<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignEmail;
use App\Models\Setting;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;
    public $timeout = 120;

    /**
     * @param Campaign $campaign
     * @param object $customer Pode ser App\Models\User ou stdClass (newsletter)
     */
    public function __construct(
        public Campaign $campaign,
        public object $customer 
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        try {
            $couponCode = $this->campaign->coupon?->code ?? '';
            
            // Constrói as mensagens
            $whatsappMessage = $this->buildMessage($this->campaign->whatsapp_template, $this->customer, $couponCode);
            $emailBody = $this->buildMessage($this->campaign->email_template, $this->customer, $couponCode);
            $emailSubject = $this->buildMessage($this->campaign->email_subject, $this->customer, $couponCode);

            // 1. Envio WhatsApp
            if (in_array($this->campaign->channel, ['whatsapp', 'both']) && !empty($this->customer->phone)) {
                $whatsAppService->sendTextMessage($whatsappMessage, $this->customer->phone);
                Log::info("WhatsApp enviado para {$this->customer->phone}");
            }

            // 2. Envio Email
            if (in_array($this->campaign->channel, ['email', 'both']) && !empty($this->customer->email)) {
                Mail::to($this->customer->email)->send(new CampaignEmail($emailSubject, $emailBody));
                Log::info("E-mail enviado para {$this->customer->email}");
            }

            // 3. Atualizar Status no Pivot (SOMENTE SE FOR USUÁRIO REGISTRADO)
            if ($this->customer instanceof User) {
                $this->campaign->audience()->updateExistingPivot($this->customer->id, [
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Falha ao enviar campanha Job: " . $e->getMessage());

            // Só tenta salvar o erro no banco se for um Usuário real
            if ($this->customer instanceof User) {
                $this->campaign->audience()->updateExistingPivot($this->customer->id, [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildMessage($template, $customer, $couponCode): string
    {
        if (is_null($template)) return '';

        $settings = Setting::first();
        $storeName = $settings->store_name ?? 'Nome da Empresa';
        
        // Garante que acessa a propriedade name, ou usa um fallback
        $customerName = $customer->name ?? 'Cliente';
        
        $message = str_replace('{nomeCliente}', $customerName, $template);
        $message = str_replace('{cupao}', $couponCode, $message);
        $message = str_replace('[Nome da Empresa]', $storeName, $message);

        return $message;
    }
}