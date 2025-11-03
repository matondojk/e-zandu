<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\Product;
// use App\Services\ReportService; // <-- REMOVIDO (Não precisamos mais)
use App\Mail\StockAlertMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use PDF; // ✅ IMPORTAR A FACHADA PDF

class SendStockAlerts extends Command
{
    protected $signature = 'stock:send-alerts';
    protected $description = 'Verifica o stock baixo e envia um alerta por e-mail com PDF.';

    /**
     * ✅ Removemos ReportService da injeção
     */
    public function handle() 
    {
        $this->info("Iniciando verificação de stock baixo...");

        // 1. Obter Configurações
        $settings = Setting::firstOrCreate([]); 
        $minimumStock = $settings->minimum_stock ?? 10;
        
        // 2. Obter E-mails Destinatários
        $recipients = $this->getRecipients($settings->stock_alert_emails);

        if (empty($recipients)) {
            $this->warn("Nenhum e-mail válido encontrado em 'stock_alert_emails'. Abortando.");
            Log::warning('[SendStockAlerts] Nenhum destinatário configurado.');
            return 0;
        }

        // 3. Query Otimizada para Stock Baixo
        $lowStockProducts = DB::table('products as p')
            ->leftJoin('stock_movements as sm', 'p.id', '=', 'sm.product_id')
            ->select('p.name', 'p.sku', DB::raw('COALESCE(SUM(sm.quantity), 0) as stock_atual'))
            ->groupBy('p.id', 'p.name', 'p.sku')
            ->having('stock_atual', '<', $minimumStock)
            ->orderBy('stock_atual', 'asc')
            ->get();

        // 4. Verificar se há produtos
        if ($lowStockProducts->isEmpty()) {
            $this->info("Nenhum produto com stock baixo (abaixo de {$minimumStock}). Nenhuma ação necessária.");
            Log::info('[SendStockAlerts] Verificação concluída. Nenhum stock baixo.');
            return 0;
        }

        $productCount = $lowStockProducts->count();
        $this->warn("ALERTA: {$productCount} produtos encontrados abaixo do stock mínimo de {$minimumStock} unidades.");

        // --- 5. ✅ GERAÇÃO DE PDF (Usando a Nova View) ---
        $pdfPath = null;
        try {
            $title = "Relatório de Stock Baixo (Abaixo de {$minimumStock} unidades)";
            
            // Prepara os dados para a view (a view espera 'products', 'title', 'minimumStock')
            $data = [
                'products' => $lowStockProducts,
                'title' => $title,
                'minimumStock' => $minimumStock
            ];
            
            // Carrega a view personalizada que você criou
            $pdf = PDF::loadView('pdfs.stock_alert', $data);

            // Define um caminho temporário
            $tempDir = storage_path('app/temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }
            $pdfPath = $tempDir . '/stock_alert_' . now()->format('YmdHis') . '.pdf';
            
            // Salva o PDF no caminho temporário
            $pdf->save($pdfPath);
            // --- Fim da Geração de PDF ---

            // 6. Enviar E-mail com Anexo (O StockAlertMail.php já está correto)
            Mail::to($recipients)->send(new StockAlertMail($pdfPath, $productCount, $minimumStock));

            $this->info("E-mail de alerta enviado para: " . implode(', ', $recipients));

        } catch (\Exception $e) {
            $this->error("Falha ao gerar PDF ou enviar e-mail: " . $e->getMessage());
            Log::error('[SendStockAlerts] Falha: ' . $e->getMessage());
        } finally {
            // 7. Limpar o PDF temporário
            if ($pdfPath && File::exists($pdfPath)) {
                File::delete($pdfPath);
                $this->info("PDF temporário limpo.");
            }
        }

        return 0;
    }

    /**
     * Helper para extrair e validar e-mails da string de configurações.
     */
    private function getRecipients(?string $emailsString): array
    {
        if (empty($emailsString)) {
            return [];
        }

        $emails = collect(preg_split('/[;,]/', $emailsString))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->toArray();
            
        return array_unique($emails);
    }
}

