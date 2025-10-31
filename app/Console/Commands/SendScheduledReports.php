<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedReport;
use App\Services\IntelligenceService;
use App\Services\ReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ScheduledReportMail;
use Illuminate\Support\Facades\File; // Importar File

class SendScheduledReports extends Command
{
    protected $signature = 'app:send-scheduled-reports {--frequency=}';
    protected $description = 'Executa relatórios agendados e envia por e-mail';

    public function handle(IntelligenceService $ai, ReportService $reporter)
    {
        $this->info("A procurar relatórios agendados...");

        // Obter a frequência (para o cron job) ou processar todos os ativos
        $frequency = $this->option('frequency');
        
        $query = SavedReport::where('is_scheduled', true);

        if ($frequency) {
            $query->where('frequency', $frequency);
            $this->info("A processar relatórios de frequência: {$frequency}");
        } else {
            $this->info("A processar todos os relatórios ativos (sem filtro de frequência).");
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            $this->info("Nenhum relatório agendado encontrado para esta frequência.");
            return 0;
        }

        foreach ($reports as $report) {
            $this->info("A processar: {$report->name}...");
            try {
                // 1. Gerar SQL
                $sql = $ai->generateQueryFromPrompt($report->prompt);
                
                // 2. Validar SQL
                if (!$ai->isSafeQuery($sql)) {
                    throw new \Exception("A IA gerou uma consulta insegura e foi bloqueada.");
                }

                // 3. Executar SQL
                $data = DB::select($sql);
                $data = json_decode(json_encode($data), true); // Converte para array

                if (empty($data)) {
                    $this->warn("Relatório '{$report->name}' não gerou dados. A saltar e-mail.");
                    continue;
                }
                
                $columns = array_keys($data[0]);

                // 4. Gerar PDF
                $pdfPath = $reporter->generatePdf($data, $columns, $report->name);

                // 5. Enviar E-mail
                Mail::to($report->recipient_email)
                    ->send(new ScheduledReportMail($report->name, $pdfPath));

                // 6. Limpar o PDF do disco
                File::delete($pdfPath);

                $this->info("... E-mail enviado para {$report->recipient_email}.");

            } catch (\Exception $e) {
                Log::error("Falha ao processar relatório agendado #{$report->id} '{$report->name}': " . $e->getMessage());
                $this->error("... Falha ao processar '{$report->name}': " . $e->getMessage());
            }
        }

        $this->info("Processo de agendamento concluído.");
        return 0;
    }
}