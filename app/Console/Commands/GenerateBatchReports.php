<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedReport;
use App\Models\Setting; // ✅ Importante para o Logo
use App\Services\IntelligenceService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use App\Mail\BatchReportsMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf; // ✅ Importante para gerar o PDF bonito

class GenerateBatchReports extends Command
{
    protected $signature = 'ai:generate-reports {--days=7 : Número de dias para a janela de análise, se aplicável.}';
    protected $description = 'Gera todos os relatórios guardados em PDF (com formatação visual) e envia por e-mail.';

    public function handle(IntelligenceService $ai)
    {
        $days = $this->option('days');
        $this->info("Iniciando a geração de relatórios em lote (Últimos {$days} dias)...");

        $reports = SavedReport::all();
        $batchDir = storage_path('app/reports/batch');

        // Garante que o diretório existe
        if (!File::exists($batchDir)) {
            File::makeDirectory($batchDir, 0755, true);
        }

        if ($reports->isEmpty()) {
            $this->warn("Nenhum relatório guardado encontrado.");
            return 0;
        }

        // ✅ Carregar Configurações (Para o Logo e Nome da Loja)
        // Tenta achar o ID padrão ou pega o primeiro
        $setting = Setting::find('00000000-0000-0000-0000-000000000001') ?? Setting::first();

        $successfulCount = 0;
        $failedReports = [];
        $generatedPdfs = [];

        foreach ($reports as $report) {
            $this->info("  -> Processando: {$report->name}...");
            $tempPath = null;

            try {
                // 1. Modifica o prompt para incluir o período
                $prompt = "{$report->prompt} (Dados dos últimos {$days} dias)";

                // 2. Gerar SQL
                $sql = $ai->generateQueryFromPrompt($prompt);

                // 3. Validar SQL
                if (!$ai->isSafeQuery($sql)) {
                    throw new \Exception("A IA gerou uma consulta insegura.");
                }

                // 4. Executar SQL
                $data = DB::select($sql);
                $data = json_decode(json_encode($data), true);

                if (empty($data)) {
                    $this->warn("     -> Não gerou dados. Salta.");
                    $failedReports[] = "{$report->name} (Sem dados)";
                    continue;
                }

                $columns = array_keys($data[0]);

                // 5. ✅ Gerar PDF com Design (Usando a View Blade)
                $filename = Str::slug($report->name) . '-' . Carbon::now()->format('YmdHis') . '.pdf';
                $finalPdfPath = $batchDir . '/' . $filename;

                $pdfData = [
                    'setting'     => $setting,
                    'reportName'  => $report->name,
                    'data'        => $data,
                    'columns'     => $columns,
                    'generatedAt' => Carbon::now()->format('d/m/Y H:i')
                ];

                // Renderiza usando o Blade 'pdfs.batch_report'
                $pdf = Pdf::loadView('pdfs.batch_report', $pdfData)
                    ->setPaper('a4', 'portrait') // Ou 'landscape' se tiver muitas colunas
                    ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

                $pdf->save($finalPdfPath);

                $this->info("     -> PDF salvo em: reports/batch/{$filename}");
                $successfulCount++;
                $generatedPdfs[] = $finalPdfPath;
            } catch (\Exception $e) {
                Log::error("Falha ao processar relatório '{$report->name}': " . $e->getMessage());
                $this->error("     -> Falha: " . $e->getMessage());
                $failedReports[] = "{$report->name} (Erro: " . $e->getMessage() . ")";
            }
        }

        // --- 6. Envio de E-mail ---
        $this->info("Finalizando e preparando e-mail...");
        $recipients = $ai->getAdminRecipients();

        if (!empty($recipients)) {
            $subject = "Relatórios Automáticos - " . Carbon::now()->format('d/m/Y');

            // Texto atualizado: Formal, direto e profissional
            $emailBody = "Prezados,\n\n";
            $emailBody .= "Informamos que o processamento dos relatórios automáticos foi concluído com sucesso.\n";
            $emailBody .= "Os documentos gerados seguem em anexo para conferência.\n\n";

            // Lista falhas apenas se existirem, de forma discreta
            if (count($failedReports) > 0) {
                $emailBody .= "Observação: Os seguintes relatórios não retornaram dados para o período analisado:\n";
                $emailBody .= implode("\n", $failedReports) . "\n\n";
            }

            $emailBody .= "Atenciosamente,\n" . (config('app.name') ?? 'Sistema');

            try {
                Mail::to($recipients)
                    ->send(new BatchReportsMail($subject, $emailBody, $generatedPdfs));

                $this->info("E-mail enviado para: " . implode(', ', $recipients));
            } catch (\Exception $e) {
                $this->error("Erro ao enviar e-mail: " . $e->getMessage());
            }
        } else {
            $this->warn("Nenhum destinatário configurado para receber os relatórios.");
        }

        // --- 7. Limpeza ---
        foreach ($generatedPdfs as $pdfPath) {
            if (File::exists($pdfPath)) File::delete($pdfPath);
        }

        return 0;
    }
}
