<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Models\Newsletter;
use App\Jobs\SendCampaignJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DispatchScheduledCampaigns extends Command
{
    protected $signature = 'campaign:dispatch-scheduled';
    protected $description = 'Verifica campanhas agendadas que estão prontas para serem enviadas e as despacha.';

    public function handle()
    {
        $now = Carbon::now();
        $this->info("Verificando campanhas agendadas em: {$now}...");

        // Busca campanhas agendadas para agora ou antes
        $campaigns = Campaign::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('Nenhuma campanha agendada para agora.');
            return 0;
        }

        foreach ($campaigns as $campaign) {
            // Tenta bloquear a campanha mudando status para processing.
            // Isso evita que outro processo pegue a mesma campanha se o cron rodar sobreposto.
            $updated = Campaign::where('id', $campaign->id)
                ->where('status', 'scheduled') // Garante que ainda está agendada
                ->update(['status' => 'processing']);

            if ($updated === 0) {
                // Outro processo já pegou essa campanha
                continue;
            }

            $this->info("Processando campanha: {$campaign->name} ({$campaign->id})");

            try {
                $customers = collect();

                // 1. Busca Audiência com base no Tipo
                if ($campaign->audience_type === 'newsletter') {
                    // Busca da tabela Newsletter
                    $customers = Newsletter::where('is_subscribed', true)->get()->map(function ($subscriber) {
                        return (object) [
                            'id' => null,
                            'email' => $subscriber->email,
                            'name' => 'Assinante',
                            'phone' => null,
                        ];
                    });
                } else {
                    // Busca da relação Users (pivot)
                    $customers = $campaign->audience()->get();
                }

                if ($customers->isEmpty()) {
                    $this->warn("Campanha {$campaign->name} não tem audiência. Marcando como 'sent' (vazia).");
                    $campaign->update(['status' => 'sent']); 
                    continue;
                }

                // 2. Despacha Jobs
                foreach ($customers as $customer) {
                    SendCampaignJob::dispatch($campaign, $customer)
                        ->onQueue('campaigns');
                }
                
                $campaign->update(['status' => 'sent']);
                
                $this->info("Despachados {$customers->count()} jobs para a campanha {$campaign->name}.");

            } catch (\Exception $e) {
                Log::error("Falha ao despachar campanha agendada {$campaign->id}: " . $e->getMessage());
                // Se falhar o despacho em massa, marca como failed para análise
                $campaign->update(['status' => 'failed']);
            }
        }

        $this->info('Processo concluído.');
        return 0;
    }
}