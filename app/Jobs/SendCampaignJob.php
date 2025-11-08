<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\User; // ✅ CORREÇÃO: Usar o modelo User
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\CampaignEmail;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $failOnTimeout = true;
    public $timeout = 120; 

    public function __construct(
        public Campaign $campaign,
        public User $customer 
    ) {}

    public function handle(WhatsAppService $whatsAppService): void
    {
        try {
            $couponCode = $this->campaign->coupon?->code ?? '';
            $whatsappMessage = $this->buildMessage($this->campaign->whatsapp_template, $this->customer, $couponCode);
            $emailBody = $this->buildMessage($this->campaign->email_template, $this->customer, $couponCode);
            $emailSubject = $this->buildMessage($this->campaign->email_subject, $this->customer, $couponCode);

            if (in_array($this->campaign->channel, ['whatsapp', 'both']) && !empty($this->customer->phone)) {
                $whatsAppService->sendTextMessage($whatsappMessage, $this->customer->phone);
            }

            if (in_array($this->campaign->channel, ['email', 'both']) && !empty($this->customer->email)) {
                Mail::to($this->customer->email)->send(new CampaignEmail($emailSubject, $emailBody));
            }

            $this->campaign->audience()->updateExistingPivot($this->customer->id, [
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Falha ao enviar campanha Job: " . $e->getMessage());
            $this->campaign->audience()->updateExistingPivot($this->customer->id, [
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    private function buildMessage($template, $customer, $couponCode): string
    {
        if(is_null($template)) return '';
        $message = str_replace('{nomeCliente}', $customer->name ?? 'Cliente', $template);
        $message = str_replace('{cupao}', $couponCode, $message);
        return $message;
    }
}