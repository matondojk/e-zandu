<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Customer; // <-- NOVO: Importar Customer
use App\Services\InvoiceService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\OrderService; // <-- NOVO: Para usar o formatPrice

class PaymentController extends Controller
{
    protected $whatsAppService;
    protected $invoiceService;
    protected $orderService; 

    public function __construct(WhatsAppService $whatsAppService, InvoiceService $invoiceService, OrderService $orderService) 
    {
        $this->whatsAppService = $whatsAppService;
        $this->invoiceService = $invoiceService;
        $this->orderService = $orderService; 
    }

    public function show(string $uuid)
    {
        // Garante que o cliente é carregado junto com o carrinho
        $cart = Cart::where('uuid', $uuid)->where('status', 'active')->with('customer')->firstOrFail();

        // Puxar os dados de pagamento dinamicamente (com cache)
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::find(1);
        });

        if (!$settings) {
            Log::error("ERRO CRÍTICO: Configurações da loja não encontradas.");
            abort(500, "Configurações da loja não encontradas.");
        }

        // --- CÁLCULO DO FRETE E TOTAL GERAL ---
        $shippingRate = $settings->shipping_rate ?? 0;
        $grandTotal = $cart->total_price + $shippingRate;
        // --- FIM DO CÁLCULO ---

        // --- PREPARAÇÃO DE DADOS PARA A VIEW ---
        $customer = $cart->customer;

        // Retorna a view
        return view('payment.form', [
            'cart' => $cart,
            'customerName' => $customer->name ?? 'Cliente Desconhecido',
            'customerAddress' => $customer->delivery_address,
            'subTotal' => $this->orderService->formatPrice($cart->total_price), // Valor dos produtos
            'shippingRate' => $this->orderService->formatPrice($shippingRate), // Frete
            'grandTotal' => $this->orderService->formatPrice($grandTotal), // Total Geral
            'iban' => $settings->iban,
            'multicaixa_express' => $settings->multicaixa_express,
        ]);
    }

    /**
     * Recebe o comprovativo de pagamento, notas e endereço.
     */
    public function store(Request $request, string $uuid)
    {
        $request->validate([
            'comprovativo' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // Max 5MB
            'order_notes' => 'nullable|string|max:1000', // Validar as notas
            'delivery_address' => 'nullable|string|max:500', // ✅ NOVO: Validação do Endereço
        ]);

        $cart = Cart::where('uuid', $uuid)->where('status', 'active')->firstOrFail();
        
        // 1. ✅ CORREÇÃO: Atualizar o Endereço de Entrega no Customer
        $newAddress = $request->input('delivery_address');
        $customer = $cart->customer;

        if ($newAddress && $customer) {
            $customer->update(['delivery_address' => $newAddress]);
        }

        // 2. Salva o comprovativo
        $path = $request->file('comprovativo')->store('comprovativos', 'public');
        $cart->payment_proof_path = $path;

        // 3. Salva as Notas do Pedido
        $cart->order_notes = $request->input('order_notes');

        $cart->save();

        // 4. Notifica o Admin
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::find(1);
        });

        $adminMsg = "🔔 Novo Comprovativo Submetido! 🔔\n\n";
        $adminMsg .= "Pedido: #{$cart->id}\n";
        $adminMsg .= "Cliente: {$cart->customer->name} ({$cart->customer->phone})\n"; 
        $adminMsg .= "Total: " . $this->orderService->formatPrice($cart->total_price) . "\n\n";

        if (!empty($cart->order_notes)) {
            $adminMsg .= "*Notas do Cliente (Tamanho/Cor):*\n";
            $adminMsg .= "{$cart->order_notes}\n\n";
        }
        
        // Se o endereço foi atualizado, inclui na notificação
        if (!empty($newAddress)) {
             $adminMsg .= "*Endereço de Entrega Atualizado:*\n{$newAddress}\n\n";
        }


        $adminMsg .= "Comprovativo: " . Storage::disk('public')->url($path) . "\n\n";
        $adminMsg .= "Por favor, aceda ao painel para verificar e aprovar o pagamento.";

        if ($settings && $settings->admin_notification_phone) {
            $this->whatsAppService->sendTextMessage($adminMsg, $settings->admin_notification_phone);
        }

        // 5. Notifica os Admins no Painel Filament
        $panelUrl = route('filament.admin.resources.orders.view', $cart);
        $this->whatsAppService->notifyAdmin(
            "Novo comprovativo submetido para o Pedido #{$cart->id}",
            $panelUrl,
            'heroicon-o-document-check'
        );

        // 6. ✅ CORREÇÃO: Passar o objeto $cart para a view
        return view('payment.success', [
            'cart' => $cart,
            'cartId' => $cart->id // Mantido por compatibilidade, mas 'cart' é suficiente
        ]);
    }
}
