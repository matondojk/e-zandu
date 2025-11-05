<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\User; // <-- Corrigido para 'User' (maiúsculo)
use App\Models\Setting;
use App\Models\PaymentMethod;
use App\Models\ShippingRate;
use App\Models\Coupon;
use App\Services\InvoiceService;
use App\Services\WhatsAppService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    /**
     * Mostra o formulário de checkout dinâmico.
     */
    public function show(string $uuid)
    {
        $cart = Cart::where('uuid', $uuid)->where('status', 'active')->with('user')->firstOrFail();

        // Buscar dados dinâmicos para o formulário
        $paymentMethods = PaymentMethod::where('is_active', true)->get();
        $shippingRates = ShippingRate::where('is_active', true)->orderBy('province_name')->get();

        if ($paymentMethods->isEmpty() || $shippingRates->isEmpty()) {
            Log::error("ERRO CRÍTICO: Métodos de Pagamento ou Taxas de Frete não configurados.");
            abort(500, "Ocorreu um erro na configuração da loja. Por favor, contacte o suporte.");
        }

        $user = $cart->user;

        // O total do carrinho (Subtotal)
        $subTotal = $cart->total_price;

        return view('payment.form', [
            'cart' => $cart,
            'userName' => $user->name ?? 'Cliente',
            'userAddress' => $user->delivery_address, // Isto será a "referência"
            'subTotalRaw' => $subTotal, // Valor numérico para JS
            'subTotalFormatted' => $this->orderService->formatPrice($subTotal), // Valor formatado para exibição
            'paymentMethods' => $paymentMethods,
            'shippingRates' => $shippingRates,
            'orderService' => $this->orderService // Passar o serviço para formatar preços no blade
        ]);
    }

    /**
     * Recebe o comprovativo, dados de entrega, frete, método de pagamento e cupão.
     */
    public function store(Request $request, string $uuid)
    {
        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'shipping_rate_id' => 'required|exists:shipping_rates,id',
            'delivery_address' => 'required|string|max:500', // Referência de Entrega
            // A obrigatoriedade do comprovativo é validada com base no método de pagamento
            'comprovativo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'order_notes' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string',
        ]);

        $cart = Cart::where('uuid', $uuid)->where('status', 'active')->with('user')->firstOrFail();
        $user = $cart->user;

        // --- 1. Validar e Buscar Modelos Essenciais ---
        $shippingRate = ShippingRate::findOrFail($request->shipping_rate_id);
        $paymentMethod = PaymentMethod::findOrFail($request->payment_method_id);

        // --- VALIDAÇÃO EXTRA: Comprovativo é obrigatório SE não for 'cash_on_delivery' ---
        if ($paymentMethod->type !== 'cash_on_delivery' && !$request->hasFile('comprovativo')) {
            return back()->withErrors(['comprovativo' => 'O comprovativo de pagamento é obrigatório para este método.'])->withInput();
        }

        // --- 2. Processar Cupão de Desconto (REVALIDAÇÃO no Sservidor) ---
        $discountAmount = 0;
        $coupon = null;
        if ($request->filled('coupon_code')) {
            $coupon = $this->validateCoupon($request->input('coupon_code'));

            if (!$coupon) {
                return back()->withErrors(['coupon_code' => 'Este cupão é inválido, expirou ou atingiu o limite de utilizações.'])->withInput();
            }

            // Calcular o desconto
            $discountAmount = $this->calculateDiscount($coupon, $cart->total_price);
        }

        // --- 3. Calcular Totais Finais ---
        $subTotal = $cart->total_price;
        $shippingAmount = $shippingRate->rate;
        // O Total Geral é Subtotal + Frete - Desconto
        $grandTotal = ($subTotal + $shippingAmount) - $discountAmount;

        // --- 4. Salvar Dados no Carrinho (Cart) ---
        $path = null;
        if ($request->hasFile('comprovativo')) {
            $path = $request->file('comprovativo')->store('comprovativos', 'public');
            $cart->payment_proof_path = $path;
        }

        $cart->order_notes = $request->input('order_notes');

        // Associar o frete e o valor
        $cart->shipping_rate_id = $shippingRate->id;
        $cart->shipping_amount = $shippingAmount;

        // Associar o método de pagamento
        $cart->payment_method_id = $paymentMethod->id;

        // Associar o cupão e o valor do desconto
        if ($coupon) {
            $cart->coupon_id = $coupon->id;
            $cart->discount_amount = $discountAmount;
            $coupon->increment('usage_count'); // Incrementar uso
        }

        $cart->save();

        // --- 5. Atualizar Referência de Entrega no Cliente ---
        $newAddressReference = $request->input('delivery_address');
        if ($newAddressReference && $user) {
            $user->update(['delivery_address' => $newAddressReference]);
        }

        // --- 6. Notificar o Admin ---
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first();
        });

        $adminMsg = "🔔 Novo Pedido para Revisão! 🔔\n\n";
        $adminMsg .= "Pedido: #{$cart->id}\n";
        $adminMsg .= "Cliente: {$user->name} ({$user->phone})\n\n";

        $adminMsg .= "*Resumo Financeiro:*\n";
        $adminMsg .= "Subtotal: " . $this->orderService->formatPrice($subTotal) . "\n";
        $adminMsg .= "Frete ({$shippingRate->province_name}): + " . $this->orderService->formatPrice($shippingAmount) . "\n";
        if ($discountAmount > 0) {
            $adminMsg .= "Desconto ({$coupon->code}): - " . $this->orderService->formatPrice($discountAmount) . "\n";
        }
        $adminMsg .= "*TOTAL GERAL: " . $this->orderService->formatPrice($grandTotal) . "*\n\n";

        $adminMsg .= "*Detalhes da Entrega e Pagamento:*\n";
        $adminMsg .= "Método Pag.: {$paymentMethod->name}\n";
        $adminMsg .= "Referência Entrega: {$newAddressReference}\n\n";

        if (!empty($cart->order_notes)) {
            $adminMsg .= "*Notas do Cliente (Tamanho/Cor):*\n";
            $adminMsg .= "{$cart->order_notes}\n\n";
        }

        if ($path) {
            $adminMsg .= "Comprovativo: " . Storage::disk('public')->url($path) . "\n\n";
            $adminMsg .= "Por favor, aceda ao painel para VERIFICAR e APROVAR o pagamento.";
        } else {
            $adminMsg .= "Pagamento: Na entrega (Cash on Delivery).\n";
            $adminMsg .= "Por favor, aceda ao painel para PROCESSAR o pedido.";
        }


        if ($settings && $settings->admin_notification_phone) {
            $this->whatsAppService->sendTextMessage($adminMsg, $settings->admin_notification_phone);
        }

        // 7. Notifica os Admins no Painel Filament
        $panelUrl = route('filament.admin.resources.vendas.view', $cart);
        $notificationTitle = $path ? "Novo comprovativo submetido" : "Novo pedido (Pag. na Entrega)";
        $this->whatsAppService->notifyAdmin(
            "$notificationTitle para o Pedido #{$cart->id}",
            $panelUrl,
            $path ? 'heroicon-o-document-check' : 'heroicon-o-truck'
        );

        // 8. Redireciona para a página de sucesso
        return view('payment.success', [
            'cart' => $cart,
            'cartId' => $cart->id
        ]);
    }


    /**
     * ======================================================================
     * NOVO MÉTODO: applyCoupon
     * Valida um cupão via AJAX e retorna o valor do desconto.
     * ======================================================================
     */
    public function applyCoupon(Request $request, string $uuid)
    {
        $request->validate(['coupon_code' => 'required|string']);

        $cart = Cart::where('uuid', $uuid)->where('status', 'active')->firstOrFail();
        $couponCode = $request->input('coupon_code');

        $coupon = $this->validateCoupon($couponCode);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupão inválido, expirado ou com limite de uso atingido.'
            ], 422); // 422 Unprocessable Entity
        }

        // Calcular o desconto
        $discountAmount = $this->calculateDiscount($coupon, $cart->total_price);

        return response()->json([
            'success' => true,
            'discountAmount' => $discountAmount, // Valor numérico para JS
            'discountFormatted' => $this->orderService->formatPrice($discountAmount), // Valor formatado
            'message' => 'Cupão aplicado com sucesso!'
        ]);
    }

    /**
     * Função helper para validar a lógica do cupão.
     */
    private function validateCoupon(string $couponCode): ?Coupon
    {
        return Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')
                    ->orWhereRaw('usage_count < usage_limit');
            })
            ->first();
    }

    /**
     * Função helper para calcular o desconto.
     */
    private function calculateDiscount(Coupon $coupon, float $subTotal): float
    {
        $discountAmount = 0;
        if ($coupon->type === 'percentage') {
            $discountAmount = ($subTotal * $coupon->value) / 100;
        } else { // 'fixed'
            $discountAmount = $coupon->value;
        }

        // Garante que o desconto não é maior que o total
        if ($discountAmount > $subTotal) {
            $discountAmount = $subTotal;
        }
        return $discountAmount;
    }
}