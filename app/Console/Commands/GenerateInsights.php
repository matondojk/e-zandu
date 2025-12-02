<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\IntelligenceService;
use App\Mail\AutomatedInsightMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use App\Models\Setting; // Importar Model Setting
use Barryvdh\DomPDF\Facade\Pdf; // Importar PDF

class GenerateInsights extends Command
{
    protected $signature = 'ai:generate-insights';
    protected $description = 'Gera e envia por e-mail um resumo semanal de performance da IA.';

    public function handle(IntelligenceService $ai)
    {
        $this->info("Iniciando a geração de insights de performance...");

        try {
            // 1. Gerar o Insight de Performance
            $analysis = $ai->generateBusinessInsights();

            // 2. Determinar o período para o assunto
            $endDate = Carbon::now()->subDay();
            $startDate = Carbon::now()->subWeek();
            $subject = "Resumo Semanal de Performance: {$startDate->format('d/m')} a {$endDate->format('d/m')}";

            // 3. Obter configurações para o PDF (Logo e Nome)
            $setting = Setting::find('00000000-0000-0000-0000-000000000001') ?? Setting::first();

            // 4. Gerar o PDF
            $pdfData = [
                'setting' => $setting,
                'analysisText' => $analysis,
                'dateRange' => "{$startDate->format('d/m')} a {$endDate->format('d/m')}"
            ];

            // Configuração para permitir imagens remotas (necessário se logo for URL)
            $pdf = Pdf::loadView('pdfs.insight', $pdfData)
                      ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);
            
            // Salvar PDF temporariamente
            $tempPath = storage_path('app/temp_insight_' . time() . '.pdf');
            $pdf->save($tempPath);

            // 5. Obter destinatários
            $recipients = $ai->getAdminRecipients();

            if (empty($recipients)) {
                $this->warn("Nenhum e-mail de destinatário válido encontrado.");
                Log::info("Insight Gerado (Não Enviado): " . $analysis);
                @unlink($tempPath); // Apaga o temp se não enviar
                return 0;
            }

            // 6. Enviar e-mail com anexo
            Mail::to($recipients)
                ->send(new AutomatedInsightMail($subject, $analysis, $tempPath));

            $this->info("Insights enviados para: " . implode(', ', $recipients));

            // 7. Limpeza: Apagar o arquivo temporário
            @unlink($tempPath);

        } catch (\Exception $e) {
            $this->error("Falha na geração/envio de insights: " . $e->getMessage());
            Log::error("Erro no comando ai:generate-insights: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}