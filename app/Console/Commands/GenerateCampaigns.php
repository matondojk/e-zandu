<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntelligenceService;
use App\Mail\AutomatedInsightMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GenerateCampaigns extends Command
{
    protected $signature = 'ai:generate-campaigns';
    protected $description = 'Analisa produtos com baixa performance e sugere campanhas promocionais/cross-sell.';

    public function handle(IntelligenceService $ai)
    {
        $this->info("Iniciando a geração de sugestões de campanhas...");

        try {
            // 1. Gerar Análise de Cross-Sell (Reutiliza a lógica que descobre o que vender junto)
            $analysis = $ai->generateCrossSellAnalysis();

            $subject = "Sugestões de Campanhas e Cross-Sell Automáticas";

            // 2. Obter destinatários
            $recipients = $ai->getAdminRecipients();

            if (empty($recipients)) {
                $this->warn("Nenhum e-mail de destinatário válido encontrado. A sugestão será apenas logada.");
                Log::info("Sugestão de Campanha (Não Enviada): " . $analysis);
                return 0;
            }

            // 3. Enviar e-mail
            Mail::to($recipients)
                ->send(new AutomatedInsightMail($subject, $analysis));

            $this->info("Sugestões de campanha geradas e enviadas para: " . implode(', ', $recipients));
        } catch (\Exception $e) {
            $this->error("Falha na geração de campanhas: " . $e->getMessage());
            Log::error("Erro no comando ai:generate-campaigns: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
