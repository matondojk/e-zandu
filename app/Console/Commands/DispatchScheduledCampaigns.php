<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Campaign;
use App\Jobs\SendCampaignJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DispatchScheduledCampaigns extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'campaign:dispatch-scheduled';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Verifica campanhas agendadas que estão prontas para serem enviadas e as despacha.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $now = Carbon::now();
        $this->info("Verificando campanhas agendadas em: {$now}...");

        // Busca campanhas que estão "scheduled" E que a hora de agendamento é agora ou no passado
        $campaignsToRun = Campaign::where('status', 'scheduled')
                                ->where('scheduled_at', '<=', $now)
                                ->get();

        if ($campaignsToRun->isEmpty()) {
            $this->info('Nenhuma campanha agendada para agora.');
            return 0;
        }

        $this->info("Encontradas {$campaignsToRun->count()} campanhas para processar.");

        foreach ($campaignsToRun as $campaign) {
            try {
                // 1. "Trava" a campanha para 'processing' para que não seja pega de novo
                $campaign->update(['status' => 'processing']);
                $this->info("Processando campanha: {$campaign->name} ({$campaign->id})");

                // 2. Busca a audiência anexada
                $customers = $campaign->audience()->get();

                if ($customers->isEmpty()) {
                    $this->warn("Campanha {$campaign->name} não tem audiência. Marcando como 'sent'.");
                    $campaign->update(['status' => 'sent']); // Ou 'failed' se preferir
                    continue;
                }

                // 3. Despacha um job para CADA cliente
                foreach ($customers as $customer) {
                    SendCampaignJob::dispatch($campaign, $customer)
                        ->onQueue('campaigns');
                }
                
                $this->info("Despachados {$customers->count()} jobs para a campanha {$campaign->name}.");

            } catch (\Exception $e) {
                Log::error("Falha ao despachar campanha agendada {$campaign->id}: " . $e->getMessage());
                $campaign->update(['status' => 'failed']); // Marca como falha se o *despacho* falhar
            }
        }

        $this->info('Verificação de campanhas agendadas concluída.');
        return 0;
    }
}