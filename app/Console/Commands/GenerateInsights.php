<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntelligenceService;
use App\Mail\AutomatedInsightMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GenerateInsights extends Command
{
    protected $signature = 'ai:generate-insights';
    protected $description = 'Gera e envia por e-mail um resumo semanal de performance da IA.';

    public function handle(IntelligenceService $ai)
    {
        $this->info("Iniciando a geração de insights de performance...");

        try {
            // 1. Gerar o Insight de Performance (Reutiliza a lógica existente)
            $analysis = $ai->generateBusinessInsights();

            // 2. Determinar o período para o assunto
            $endDate = Carbon::now()->subDay();
            $startDate = Carbon::now()->subWeek();
            $subject = "Resumo Semanal de Performance: {$startDate->format('d/m')} a {$endDate->format('d/m')}";

            // 3. Obter destinatários
            $recipients = $ai->getAdminRecipients();

            if (empty($recipients)) {
                $this->warn("Nenhum e-mail de destinatário válido encontrado. O insight será apenas logado.");
                Log::info("Insight Gerado (Não Enviado): " . $analysis);
                return 0;
            }

            // 4. Enviar e-mail
            Mail::to($recipients)
                ->send(new AutomatedInsightMail($subject, $analysis));

            $this->info("Insights gerados e enviados para: " . implode(', ', $recipients));

        } catch (\Exception $e) {
            $this->error("Falha na geração/envio de insights: " . $e->getMessage());
            Log::error("Erro no comando ai:generate-insights: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}