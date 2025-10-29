<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StockAlert;
use App\Models\Stock;
use App\Notifications\ProductRestocked;
use App\Services\WhatsAppService;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStockAlerts extends Command
{
    protected $signature = 'stock:process-alerts';
    protected $description = 'Verifica o estoque e envia notificações (WhatsApp e Email) aos clientes pendentes.';

    protected $whatsAppService;
    protected $orderService;

    public function __construct(WhatsAppService $whatsAppService, OrderService $orderService)
    {
        parent::__construct();
        $this->whatsAppService = $whatsAppService;
        $this->orderService = $orderService;
    }

    public function handle()
    {
        $this->info('Iniciando processamento de alertas de estoque...');
        
        $pendingAlerts = StockAlert::where('is_notified', false)
            ->with(['product', 'customer'])
            ->get();
            
        $alertsToDispatch = collect();
        $defaultStock = Stock::where('is_default', true)->first();

        if (!$defaultStock) {
            $this->error('Não foi encontrado um Armazém Padrão (is_default=true). Alertas não processados.');
            Log::error('ERRO: Armazém Padrão não encontrado para processar alertas de estoque.');
            return 1;
        }

        // 1. Filtrar Alertas com Estoque Reposto
        foreach ($pendingAlerts as $alert) {
            // Assume que Product Model tem o método getStockIn(int $stockId)
            if ($alert->product && method_exists($alert->product, 'getStockIn') && $alert->product->getStockIn($defaultStock->id) > 0) {
                 $alertsToDispatch->push($alert);
            }
        }
        
        $count = 0;
        
        // 2. Enviar Notificações e Marcar como Processado (Transação para segurança)
        DB::transaction(function () use ($alertsToDispatch, &$count) {
            foreach ($alertsToDispatch as $alert) {
                try {
                    $product = $alert->product;
                    $customer = $alert->customer;
                    $isEmailSent = false;
                    $isWhatsappSent = false;

                    // A. ENVIAR NOTIFICAÇÃO POR EMAIL
                    // Assume que o Model Customer usa o Notifiable trait e tem a coluna 'email'
                    if ($customer->email) {
                         $customer->notify(new ProductRestocked($product)); 
                         $isEmailSent = true;
                    }
                    
                    // B. ENVIAR NOTIFICAÇÃO POR WHATSAPP
                    try {
                        $this->sendWhatsAppAlert($customer, $product);
                        $isWhatsappSent = true;
                    } catch (\Exception $e) {
                        Log::warning("Falha ao enviar WhatsApp para {$customer->phone}: " . $e->getMessage());
                    }

                    if ($isEmailSent || $isWhatsappSent) {
                        // C. MARCAR ALERTA COMO PROCESSADO
                        $alert->is_notified = true;
                        $alert->save();
                        $count++;
                        $this->info("Alerta #{$alert->id} despachado. (Email: " . ($isEmailSent ? 'SIM' : 'NÃO') . ", WA: " . ($isWhatsappSent ? 'SIM' : 'NÃO') . ").");
                    }

                } catch (\Exception $e) {
                    Log::error("Falha CRÍTICA ao processar alerta para cliente #{$alert->customer_id}: " . $e->getMessage());
                }
            }
        });

        $this->info("Processamento concluído. {$count} notificações despachadas.");
        return 0;
    }
    
    /**
     * Lógica de envio da mensagem interativa do WhatsApp.
     */
    protected function sendWhatsAppAlert(\App\Models\Customer $customer, \App\Models\Product $product)
    {
        $price = $product->promotional_price > 0 ? $product->promotional_price : $product->price;
        $priceString = $product->promotional_price > 0 
            ? "Preço: ~" . $this->orderService->formatPrice($product->price) . "~ *Kz " . $this->orderService->formatPrice($product->promotional_price) . "* (Promoção!)"
            : "Preço: *Kz " . $this->orderService->formatPrice($product->price) . "*";

        $body = "🎉 Boas notícias, " . ($customer->name ?? 'Cliente') . "! 🎉\n\n";
        $body .= "O produto *{$product->name}* foi reposto no nosso estoque!\n\n";
        $body .= $priceString . "\n";
        
        $buttons = [
            ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Comprar Agora'], 
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'],
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Clique abaixo para garantir o seu!",
            $product->image_url,
            $buttons
        );
    }
}