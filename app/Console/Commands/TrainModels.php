<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\IntelligenceService;
use App\Mail\AutomatedInsightMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainModels extends Command
{
    protected $signature = 'ai:train-models';
    protected $description = 'Atualiza modelos preditivos e gera relatório PDF detalhado.';

    public function handle(IntelligenceService $ai)
    {
        $this->info("Iniciando atualização de modelos preditivos...");
        
        $success = false;
        $forecastData = [];
        $crossSellAnalysis = "";

        try {
            // A. Previsão de Vendas (Retorna Array: historical, forecast, analysis)
            $this->info(" -> Gerando nova previsão de vendas...");
            $forecastData = $ai->generateSalesForecast(); 
            Cache::put('ai_sales_forecast_data', $forecastData, now()->addDays(7)); 
            
            // B. Análise de Cross-Sell (Retorna String Markdown)
            $this->info(" -> Gerando nova análise de cross-sell...");
            $crossSellAnalysis = $ai->generateCrossSellAnalysis();
            Cache::put('ai_cross_sell_analysis', $crossSellAnalysis, now()->addDays(7));

            // C. Limpar cache de BI
            Cache::forget('ai_business_insights');
            
            $success = true;

        } catch (\Exception $e) {
            $this->error("Falha na atualização: " . $e->getMessage());
            Log::error("Erro TrainModels: " . $e->getMessage());
            return 1;
        }

        // --- Geração de PDF e Envio ---
        $recipients = $ai->getAdminRecipients();
        
        if ($success && !empty($recipients)) {
            try {
                $setting = Setting::find('00000000-0000-0000-0000-000000000001') ?? Setting::first();

                // ✅ AQUI ESTÁ A MUDANÇA: Passamos os dados da IA para a View
                $pdfData = [
                    'setting' => $setting,
                    'forecast' => $forecastData,      // Array com dados e análise
                    'crossSell' => $crossSellAnalysis // String Markdown
                ];

                $pdf = Pdf::loadView('pdfs.model_report', $pdfData)
                          ->setOptions(['isRemoteEnabled' => true, 'defaultFont' => 'sans-serif']);

                $tempPath = storage_path('app/temp_models_' . time() . '.pdf');
                $pdf->save($tempPath);

                $subject = "Relatório de Inteligência Artificial - " . date('d/m/Y');
                
                // Texto simples para o corpo do email (o detalhe está no PDF)
                $emailBody = "Os modelos de IA foram treinados com novos dados de vendas e comportamento.\n\n" .
                             "**Destaque da Previsão:**\n" . 
                             \Illuminate\Support\Str::limit($forecastData['analysis'] ?? 'Análise indisponível', 150) . 
                             "\n\nVeja o PDF em anexo para os gráficos e recomendações completas.";

                Mail::to($recipients)
                    ->send(new AutomatedInsightMail($subject, $emailBody, $tempPath));
                
                $this->info("Relatório enviado para: " . implode(', ', $recipients));
                @unlink($tempPath);

            } catch (\Exception $e) {
                $this->error("Erro ao gerar PDF/Email: " . $e->getMessage());
                Log::error("Erro PDF TrainModels: " . $e->getMessage());
            }
        }

        return 0;
    }
}