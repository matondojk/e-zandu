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

    /**
     * Gera a fatura em PDF, salva no storage público e retorna o caminho RELATIVO.
     *
     * @param Cart $cart
     * @return string O caminho relativo do arquivo (ex: 'invoices/fatura-123.pdf')
     */
    public function generate(Cart $cart): string
    {
        // 1. ✅ Carregar dados necessários (relações usadas na Fatura Blade)
        $cart->load('items.product', 'user', 'shippingRate', 'paymentMethod', 'coupon');
        
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first() ?? new Setting(); 
        });

        // 2. ✅ CÁLCULO ATUALIZADO
        // Os valores agora vêm diretamente do carrinho, que foi salvo no 'store'
        $shippingAmount = $cart->shipping_amount ?? 0;
        $discountAmount = $cart->discount_amount ?? 0;
        $grandTotal = ($cart->total_price + $shippingAmount) - $discountAmount;

        // 3. Converter Logo para Base64
        $logoBase64 = null;
        $logoMime = null;
        if ($settings->logo_dark_path && Storage::disk('public')->exists($settings->logo_path)) {
            try {
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
            // 'shippingRate' => $shippingRate, // ❌ Removido (obsoleto, usamos $cart->shipping_amount)
            'grandTotal' => $grandTotal, // ✅ Passa o total JÁ calculado
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'issueDate' => now()->format('d/m/Y H:i')
        ];

        // 5. ✅ VIEW CORRIGIDA
        // Aponta para 'resources/views/invoice.blade.php'
        // (O seu ficheiro antigo era 'invoices.template')
        $pdf = Pdf::loadView('invoices.template', $data);

        // 6. ✅ CAMINHO E SALVAMENTO CORRIGIDOS
        // Cria o nome do ficheiro com o subdiretório 'invoices'
        $filename = 'invoices/fatura-' . $cart->id . '-' . time() . '.pdf';
        
        // Salva o PDF no disco 'public' (que aponta para 'storage/app/public')
        Storage::disk('public')->put($filename, $pdf->output());

        // 7. ✅ RETORNO CORRIGIDO
        // Retorna o caminho RELATIVO (ex: 'invoices/fatura-123.pdf')
        // Isto é o que o 'ViewOrder.php' espera
        return $filename; 
    }
}