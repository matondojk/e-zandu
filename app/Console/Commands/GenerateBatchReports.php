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
use Illuminate\Support\Str;
use App\Mail\AutomatedInsightMail; // ✅ Importar Mailable
use Illuminate\Support\Facades\Mail; // ✅ Importar Mail
use ZipArchive; // ✅ Importar ZipArchive

class GenerateBatchReports extends Command
{
    protected $signature = 'ai:generate-reports {--days=7 : Número de dias para a janela de análise, se aplicável.}';
    protected $description = 'Gera todos os relatórios guardados em PDF, compacta-os e envia por e-mail.';

    public function handle(IntelligenceService $ai, ReportService $reporter)
    {
        $days = $this->option('days');
        $this->info("Iniciando a geração de relatórios em lote (Últimos {$days} dias)...");
        
        $reports = SavedReport::all();
        $batchDir = storage_path('app/reports/batch');
        
        if (!File::exists($batchDir)) {
            File::makeDirectory($batchDir, 0755, true);
        }

        if ($reports->isEmpty()) {
            $this->warn("Nenhum relatório guardado encontrado.");
            return 0;
        }
        
        $successfulCount = 0;
        $failedReports = [];
        $generatedPdfs = []; // ✅ Armazena os caminhos dos PDFs gerados

        foreach ($reports as $report) {
            $this->info("  -> Processando: {$report->name}...");
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
                $filename = Str::slug($report->name) . '-' . Carbon::now()->format('YmdHis') . '.pdf';
                
                // Reutiliza a função de gerar PDF, que cria um ficheiro temporário
                $tempPath = $reporter->generatePdf($data, $columns, $report->name); 
                
                // Move o ficheiro para a pasta batch e renomeia
                $finalPdfPath = $batchDir . '/' . $filename;
                File::move($tempPath, $finalPdfPath);
                
                $this->info("     -> PDF salvo em: reports/batch/{$filename}");
                $successfulCount++;
                $generatedPdfs[] = $finalPdfPath; // ✅ Adiciona ao array para zippar

            } catch (\Exception $e) {
                // Limpa o ficheiro temporário se a geração/movimentação falhar
                if ($tempPath && File::exists($tempPath)) {
                    File::delete($tempPath);
                }
                Log::error("Falha ao processar relatório em lote '{$report->name}': " . $e->getMessage());
                $this->error("     -> Falha: " . $e->getMessage());
                $failedReports[] = "{$report->name} (Erro: " . $e->getMessage() . ")";
            }
        }

        // --- 5. Compactação (ZIP) e Envio de E-mail ---
        $this->info("Finalizando e preparando e-mail...");
        $recipients = $ai->getAdminRecipients();
        $zipPath = null; // Caminho para o .zip

        if ($successfulCount > 0) {
            // Se houver PDFs, cria o ZIP
            $zip = new ZipArchive;
            $zipFileName = 'Relatorios-Lote-' . Carbon::now()->format('YmdHis') . '.zip';
            $zipPath = $batchDir . '/' . $zipFileName; // Salva o zip na mesma pasta batch

            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                foreach ($generatedPdfs as $pdfPath) {
                    $zip->addFile($pdfPath, basename($pdfPath)); // Adiciona o ficheiro ao zip
                }
                $zip->close();
                $this->info(" -> Arquivo ZIP criado: {$zipFileName}");
            } else {
                $this->error(" -> Falha ao criar arquivo ZIP.");
                $zipPath = null; // Garante que não tentamos anexar um zip falhado
            }
        }

        // Envia o e-mail de notificação (com ou sem anexo)
        if (!empty($recipients)) {
            $subject = "✅ Relatórios em Lote Gerados ({$successfulCount} de {$reports->count()})";
            $emailBody = "
Prezado(a) Administrador(a),

A rotina automática de geração de relatórios em lote foi concluída.

" . ($zipPath ? "Em anexo segue um arquivo .zip contendo os {$successfulCount} relatórios gerados com sucesso." : "Não foi gerado nenhum PDF para anexar.") . "

**Detalhes:**
* **Total de Relatórios Processados:** {$reports->count()}
* **Relatórios Gerados com Sucesso (PDF):** {$successfulCount}
* **Localização no Servidor:** `storage/app/reports/batch/`

" . (empty($failedReports) ? 
"Todos os relatórios foram gerados com sucesso." : 
"**⚠️ Relatórios que Falharam:**\n" . implode("\n", $failedReports));

            try {
                // ✅ Passa o $zipPath (que é null se falhar) para o Mailable
                Mail::to($recipients)
                    ->send(new AutomatedInsightMail($subject, $emailBody, $zipPath));
                
                $this->info("Notificação enviada para: " . implode(', ', $recipients));

            } catch (\Exception $e) {
                $this->error("Falha ao enviar e-mail de notificação: " . $e->getMessage());
                Log::error("Erro ao enviar notificação do GenerateBatchReports: " . $e->getMessage());
            }
        } else {
            $this->warn("Nenhum destinatário encontrado para notificação.");
        }

        // --- 6. Limpeza ---
        $this->info("Limpando arquivos temporários...");
        foreach ($generatedPdfs as $pdfPath) {
            File::delete($pdfPath); // Apaga os PDFs individuais
        }
        if ($zipPath && File::exists($zipPath)) {
            File::delete($zipPath); // Apaga o ZIP após o envio
        }
        $this->info("Limpeza concluída.");

        $this->info("Processo de geração em lote concluído. {$successfulCount} relatórios gerados.");
        return 0;
    }
}