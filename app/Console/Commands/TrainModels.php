<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\IntelligenceService;
use App\Mail\AutomatedInsightMail; // ✅ Importar o Mailable
use Illuminate\Support\Facades\Mail; // ✅ Importar a Fachada Mail

class TrainModels extends Command
{
    protected $signature = 'ai:train-models';
    protected $description = 'Atualiza dados de modelos preditivos (previsão de vendas e comportamento de clientes) limpando o cache.';

    public function handle(IntelligenceService $ai)
    {
        $this->info("Iniciando atualização de modelos preditivos e de comportamento...");
        
        // --- 1. Lógica Central de Atualização (O que o modelo faria) ---
        $success = false;
        $errorMessage = null;

        try {
            // A. Gerar NOVOS DADOS de Previsão de Vendas
            $this->info(" -> Gerando nova previsão de vendas...");
            $forecastData = $ai->generateSalesForecast();
            Cache::put('ai_sales_forecast_data', $forecastData, now()->addDays(7)); 
            $this->info("    ... Previsão salva no cache.");


            // B. Gerar NOVOS DADOS de Análise de Cross-Sell
            $this->info(" -> Gerando nova análise de comportamento do cliente (cross-sell)...");
            $crossSellAnalysis = $ai->generateCrossSellAnalysis();
            Cache::put('ai_cross_sell_analysis', $crossSellAnalysis, now()->addDays(7));
            $this->info("    ... Análise de Cross-Sell salva no cache.");


            // C. Limpar cache de insights de BI
            Cache::forget('ai_business_insights');
            $this->info(" -> Cache de Business Insights limpo. Será gerado um novo na próxima visita.");
            
            $success = true;

        } catch (\Exception $e) {
            $errorMessage = "Falha na simulação de treino/atualização: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error("Erro no comando ai:train-models: " . $e->getMessage());
            return 1;
        }

        // --- 2. Envio de E-mail de Notificação ---
        $recipients = $ai->getAdminRecipients();
        
        if ($success && !empty($recipients)) {
            $subject = "Atualização de Modelos IA Concluída";
            $emailBody = "
            Prezado(a) Administrador(a),
            
            Os modelos de Inteligência Artificial foram **atualizados e recarregados** com sucesso:
            
            * **Previsão de Vendas:** Novos dados gerados e prontos para visualização.
            * **Análise de Cross-Sell:** Novos padrões de compra detetados e em cache.
            * **Insights de BI:** O cache foi limpo; o próximo resumo proativo usará os dados mais recentes.
            
            Obrigado,
            Automação IA
            ";

            try {
                Mail::to($recipients)
                    ->send(new AutomatedInsightMail($subject, $emailBody));
                
                $this->info("Notificação de sucesso enviada para: " . implode(', ', $recipients));

            } catch (\Exception $e) {
                $this->error("Falha ao enviar e-mail de notificação: " . $e->getMessage());
                Log::error("Erro ao enviar notificação do TrainModels: " . $e->getMessage());
            }
        } elseif (!$success) {
            // Se falhou (entrou no bloco catch), o log de erro já foi escrito.
            $this->warn("A rotina falhou. E-mail de notificação não enviado.");
        }


        // --- 3. Log e Finalização ---
        sleep(2); 

        Log::info("Modelos de IA atualizados. Cache de previsão e cross-sell recarregados.");

        $this->info("Atualização de modelos concluída com sucesso. Os dados no cache foram renovados.");
        return 0;
    }
}
