<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Mail\StockAlertMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use Barryvdh\DomPDF\Facade\Pdf;

class SendStockAlerts extends Command
{
    protected $signature = 'stock:send-alerts';
    protected $description = 'Verifica o stock baixo e envia um alerta por e-mail com PDF.';

    public function handle() 
    {
        $this->info("Iniciando verificação de stock baixo...");

        // 1. Obter Configurações
        $settings = Setting::find('00000000-0000-0000-0000-000000000001') ?? Setting::firstOrCreate([]); 
        $minimumStock = $settings->minimum_stock ?? 10;
        
        // 2. Obter E-mails Destinatários
        $recipients = $this->getRecipients($settings->stock_alert_emails);

        if (empty($recipients)) {
            $this->warn("Nenhum e-mail configurado. Abortando.");
            Log::warning('[SendStockAlerts] Sem destinatários.');
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
            $this->info("Nenhum produto com stock baixo. Tudo ok.");
            return 0;
        }

        $productCount = $lowStockProducts->count();
        $this->warn("ALERTA: {$productCount} produtos abaixo do stock mínimo.");

        // --- 5. GERAÇÃO DE PDF ---
        $pdfPath = null;
        try {
            // ✅ Passamos 'settings' para a view
            $data = [
                'products' => $lowStockProducts,
                'minimumStock' => $minimumStock,
                'setting' => $settings // <-- IMPORTANTE
            ];
            
            $pdf = Pdf::loadView('pdfs.stock_alert', $data)
                      ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

            $tempDir = storage_path('app/temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }
            $pdfPath = $tempDir . '/stock_alert_' . now()->format('YmdHis') . '.pdf';
            
            $pdf->save($pdfPath);

            // 6. Enviar E-mail
            Mail::to($recipients)->send(new StockAlertMail($pdfPath, $productCount, $minimumStock));

            $this->info("Alerta enviado para: " . implode(', ', $recipients));

        } catch (\Exception $e) {
            $this->error("Erro: " . $e->getMessage());
            Log::error('[SendStockAlerts] Falha: ' . $e->getMessage());
        } finally {
            if ($pdfPath && File::exists($pdfPath)) {
                File::delete($pdfPath);
            }
        }

        return 0;
    }

    private function getRecipients(?string $emailsString): array
    {
        if (empty($emailsString)) return [];
        return collect(preg_split('/[;,]/', $emailsString))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->toArray();
    }
}