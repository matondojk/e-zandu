<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedReport;
use App\Services\IntelligenceService;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str; // Necessário para Str::slug
use App\Mail\AutomatedInsightMail; // ✅ Importar o Mailable
use Illuminate\Support\Facades\Mail; // ✅ Importar a Fachada Mail

class GenerateBatchReports extends Command
{
    protected $signature = 'ai:generate-reports {--days=7 : Número de dias para a janela de análise, se aplicável.}';
    protected $description = 'Gera todos os relatórios guardados em PDF e salva no disco (storage/app/reports/batch).';

    public function handle(IntelligenceService $ai, ReportService $reporter)
    {
        $days = $this->option('days');
        $this->info("Iniciando a geração de relatórios em lote (Últimos {$days} dias)...");
        
        $reports = SavedReport::all(); // Pega todos os relatórios guardados
        $batchDir = storage_path('app/reports/batch');
        
        // 1. Cria o diretório de batch
        if (!File::exists($batchDir)) {
            File::makeDirectory($batchDir, 0755, true);
        }

        if ($reports->isEmpty()) {
            $this->warn("Nenhum relatório guardado encontrado.");
            return 0;
        }
        
        $successfulCount = 0;
        $failedReports = []; // Para registar falhas

        foreach ($reports as $report) {
            $this->info("  -> Processando: {$report->name}...");
            
            // Variável para armazenar o caminho temporário do PDF (antes de ser movido)
            $tempPath = null; 
            
            try {
                // Modifica o prompt para incluir o período
                $prompt = "{$report->prompt} (Dados dos últimos {$days} dias)";

                // 2. Gerar SQL
                $sql = $ai->generateQueryFromPrompt($prompt);
                
                // 3. Validar e Executar SQL
                if (!$ai->isSafeQuery($sql)) {
                    throw new \Exception("A IA gerou uma consulta insegura e foi bloqueada.");
                }

                $data = DB::select($sql);
                $data = json_decode(json_encode($data), true);

                if (empty($data)) {
                    $this->warn("     -> Não gerou dados. Salta.");
                    $failedReports[] = "{$report->name} (Não gerou dados)";
                    continue;
                }
                
                $columns = array_keys($data[0]);
                
                // 4. Gerar PDF e salvar no diretório batch
                $filename = Str::slug($report->name) . '-' . Carbon::now()->format('YmdHis') . '.pdf'; // Adiciona H:i:s para unicidade
                
                // Reutiliza a função de gerar PDF, que cria um ficheiro temporário
                $tempPath = $reporter->generatePdf($data, $columns, $report->name); 
                
                // Move o ficheiro para a pasta batch e renomeia
                File::move($tempPath, $batchDir . '/' . $filename);
                
                $this->info("     -> PDF salvo em: reports/batch/{$filename}");
                $successfulCount++;

            } catch (\Exception $e) {
                // Se houver um ficheiro temporário criado, certifique-se de que é apagado
                if ($tempPath && File::exists($tempPath)) {
                    File::delete($tempPath);
                }
                Log::error("Falha ao processar relatório em lote '{$report->name}': " . $e->getMessage());
                $this->error("     -> Falha: " . $e->getMessage());
                $failedReports[] = "{$report->name} (Erro: " . $e->getMessage() . ")";
            }
        }

        // --- 5. Envio de Notificação de Sucesso (O Novo Recurso) ---
        $this->info("Finalizando e enviando notificação...");
        $recipients = $ai->getAdminRecipients();
        
        if (!empty($recipients)) {
            $subject = "Relatórios em Lote Gerados ({$successfulCount} de {$reports->count()})";
            $emailBody = "
            Prezado(a) Administrador(a),
            
            A rotina automática de geração de relatórios em lote foi concluída.
            
            **Detalhes:**
            * **Total de Relatórios Processados:** {$reports->count()}
            * **Relatórios Gerados com Sucesso (PDF):** {$successfulCount}
            * **Localização no Servidor:** `storage/app/reports/batch/`
            
            " . (empty($failedReports) ? 
            "Todos os relatórios foram gerados com sucesso." : 
            "**Relatórios que Falharam:**\n" . implode("\n", $failedReports));

            try {
                Mail::to($recipients)
                    ->send(new AutomatedInsightMail($subject, $emailBody));
                
                $this->info("Notificação enviada para: " . implode(', ', $recipients));

            } catch (\Exception $e) {
                $this->error("Falha ao enviar e-mail de notificação: " . $e->getMessage());
                Log::error("Erro ao enviar notificação do GenerateBatchReports: " . $e->getMessage());
            }
        } else {
            $this->warn("Nenhum destinatário encontrado para notificação.");
        }

        $this->info("Processo de geração em lote concluído. {$successfulCount} relatórios gerados.");
        return 0;
    }
}
