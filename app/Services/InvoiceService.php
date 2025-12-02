<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function generate(Cart $cart): string
    {
        // 1. Carregar dados
        $cart->load('items.product', 'user', 'shippingRate', 'paymentMethod', 'coupon');
        
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first() ?? new Setting(); 
        });

        // 2. Cálculos
        $shippingAmount = $cart->shipping_rate_id ? ($cart->shippingRate->rate ?? 0) : 0;
        
        $subtotal = 0;
        foreach($cart->items as $item) {
            $subtotal += $item->price * $item->quantity;
        }
        
        $discountAmount = $cart->discount_amount ?? 0;
        $grandTotal = ($subtotal - $discountAmount) + $shippingAmount;

        // 3. Logo (com verificação robusta)
        $logoBase64 = null;
        $logoMime = null;
        $logoToUse = $settings->logo_dark_path ?? $settings->logo_light_path;

        if ($logoToUse) {
            try {
                $contents = null;
                if (filter_var($logoToUse, FILTER_VALIDATE_URL)) {
                    $contents = @file_get_contents($logoToUse);
                } elseif (Storage::disk('public')->exists($logoToUse)) {
                    $path = Storage::disk('public')->path($logoToUse);
                    $contents = @file_get_contents($path);
                }

                if ($contents) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $logoMime = $finfo->buffer($contents);
                    $logoBase64 = base64_encode($contents);
                }
            } catch (\Exception $e) {
                Log::error("PDF Logo Error: " . $e->getMessage());
            }
        }

        // 4. Preparar dados
        $data = [
            'cart' => $cart,
            'settings' => $settings,
            'subtotal' => $subtotal,
            'shippingAmount' => $shippingAmount,
            'discountAmount' => $discountAmount,
            'grandTotal' => $grandTotal,
            'logoBase64' => $logoBase64,
            'logoMime' => $logoMime,
            'issueDate' => now()->format('d/m/Y H:i')
        ];

        // 5. Gerar PDF
        $pdf = Pdf::loadView('invoices.template', $data);
        $content = $pdf->output();

        // 6. Salvar (CORREÇÃO DE DIRETÓRIO)
        $directory = 'invoices';
        $filename = $directory . '/fatura-' . $cart->id . '.pdf'; // Removi o time() para evitar lixo, ou mantenha se preferir histórico

        // Garante que o diretório existe
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }
        
        // Tenta salvar
        $saved = Storage::disk('public')->put($filename, $content);

        if (!$saved) {
            throw new \Exception("Falha ao escrever o ficheiro PDF no disco. Verifique permissões.");
        }

        return $filename; 
    }
}