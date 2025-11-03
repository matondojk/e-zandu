<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Setting;
// Use a fachada correta
use Barryvdh\DomPDF\Facade\Pdf; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    
    public function __construct()
    {
        //
    }

    public function generate(Cart $cart): string
    {
        // 1. Carregar dados necessários
        $cart->load('items.product', 'customer');
        
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first() ?? new Setting(); 
        });

        // 2. Calcular totais
        $shippingRate = $settings->shipping_rate ?? 0;
        $grandTotal = $cart->total_price + $shippingRate;

        // 3. CORREÇÃO DO LOGO: Converter para Base64
        $logoBase64 = null;
        $logoMime = null;
        if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
            try {
                // (O logo continua a vir do 'storage/public', o que está correto)
                $path = Storage::disk('public')->path($settings->logo_path);
                $contents = file_get_contents($path);
                $logoMime = mime_content_type($path);
                $logoBase64 = base64_encode($contents);
            } catch (\Exception $e) {
                Log::error("Falha ao carregar logo para PDF: " . $e->getMessage());
            }
        }

        // 4. Passar TODOS os dados para a view
        $data = [
            'cart' => $cart,
            'settings' => $settings,
            'shippingRate' => $shippingRate,
            'grandTotal' => $grandTotal,
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'issueDate' => now()->format('d/m/Y H:i')
        ];

        // Carrega a view 'invoices.template'
        $pdf = Pdf::loadView('invoices.template', $data);

        // 5. ✅ CORREÇÃO DO CAMINHO (Como você pediu): Salvar em public/invoices
        $filename = 'fatura-' . $cart->id . '-' . time() . '.pdf';
        $directory = public_path('invoices'); // <--- MUDADO DE storage_path PARA public_path
        $path = $directory . '/' . $filename; 

        // Cria o diretório se não existir
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        // Salva o PDF no servidor
        $pdf->save($path);

        // 6. ✅ CORREÇÃO DO URL: Retornar o link público direto
        // (Sem 'storage/' no caminho)
        return asset('invoices/' . $filename); 
    }
}