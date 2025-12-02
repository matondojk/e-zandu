<?php

namespace App\Livewire\Site\Checkout;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Cart;
use App\Models\ShippingRate;
use App\Models\PaymentMethod;
use App\Models\Coupon;
use Illuminate\Support\Facades\Storage;

class WhatsappCheckout extends Component
{
    use WithFileUploads;

    public $cart;
    public $uuid; // UUID do carrinho vindo da rota

    // Dados do Formulário
    public $delivery_address;
    public $order_notes;
    public $selectedShippingRateId;
    public $selectedPaymentMethodId;
    public $proof; // Arquivo do comprovativo

    // Cupão
    public $couponCodeInput = '';
    public $couponApplied = null;

    // Dados Auxiliares
    public $shippingRates;
    public $paymentMethods;

    // Totais
    public $subtotal = 0;
    public $shippingCost = 0;
    public $discountAmount = 0;
    public $total = 0;

    public function mount($uuid)
    {
        $this->uuid = $uuid;

        // Buscar o carrinho pelo UUID
        $this->cart = Cart::with(['items.product', 'user'])->where('uuid', $uuid)->firstOrFail();

        // Se já estiver pago ou cancelado, redirecionar
        if (in_array($this->cart->status, ['paid', 'completed', 'cancelled'])) {
            return redirect()->route('order.success', ['cartId' => $this->cart->id]);
        }

        // Carregar dados iniciais
        $this->delivery_address = $this->cart->user->delivery_address ?? '';
        $this->shippingRates = ShippingRate::where('is_active', true)->get();
        $this->paymentMethods = PaymentMethod::where('is_active', true)->get();

        // Calcular subtotal dos itens já existentes no carrinho
        $this->subtotal = $this->cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $this->calculateTotals();
    }

    // Atualiza totais quando seleciona frete
    public function updatedSelectedShippingRateId($value)
    {
        $rate = $this->shippingRates->find($value);
        $this->shippingCost = $rate ? $rate->rate : 0;
        $this->calculateTotals();
    }

    // Aplica o Cupão
    public function applyCoupon()
    {
        $this->validate(['couponCodeInput' => 'required'], ['couponCodeInput.required' => 'Digite o código.']);

        $coupon = Coupon::where('code', $this->couponCodeInput)->first();

        if (!$coupon || !$coupon->is_active) {
            $this->addError('couponCodeInput', 'Cupão inválido ou inativo.');
            return;
        }

        // Lógica simples de validade (pode expandir conforme necessidade)
        if ($coupon->expires_at && $coupon->expires_at < now()) {
            $this->addError('couponCodeInput', 'Cupão expirado.');
            return;
        }

        $this->couponApplied = $coupon;
        $this->couponCodeInput = '';
        $this->calculateTotals();

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Desconto aplicado!']);
    }

    public function removeCoupon()
    {
        $this->couponApplied = null;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // Calcular Desconto
        $this->discountAmount = 0;
        if ($this->couponApplied) {
            if ($this->couponApplied->type === 'percentage') {
                $this->discountAmount = $this->subtotal * ($this->couponApplied->value / 100);
            } else {
                $this->discountAmount = $this->couponApplied->value;
            }
            // Garante que o desconto não é maior que o subtotal
            $this->discountAmount = min($this->discountAmount, $this->subtotal);
        }

        // Total Final
        $this->total = ($this->subtotal - $this->discountAmount) + $this->shippingCost;
    }

    public function submit()
    {
        $this->validate([
            'selectedShippingRateId' => 'required',
            'selectedPaymentMethodId' => 'required',
            'delivery_address' => 'required|min:5',
        ], [
            'selectedShippingRateId.required' => 'Selecione uma opção de entrega.',
            'selectedPaymentMethodId.required' => 'Selecione um método de pagamento.',
            'delivery_address.required' => 'Informe o endereço de entrega.',
        ]);

        // Verificar se o método de pagamento exige comprovativo
        $method = $this->paymentMethods->find($this->selectedPaymentMethodId);
        $isManualPayment = in_array($method->type, ['bank_transfer', 'express']);

        if ($isManualPayment) {
            $this->validate([
                'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ], [
                'proof.required' => 'O comprovativo é obrigatório para este método.',
                'proof.max' => 'O ficheiro deve ter no máximo 5MB.'
            ]);
        }

        // Upload do Comprovativo (se houver)
        $proofPath = null;
        if ($this->proof) {
            $proofPath = $this->proof->store('payment_proofs', 'public');
        }

        // Atualizar Carrinho/Pedido
        $this->cart->update([
            'shipping_rate_id' => $this->selectedShippingRateId,
            'payment_method_id' => $this->selectedPaymentMethodId,
            'coupon_id' => $this->couponApplied->id ?? null,
            'discount_amount' => $this->discountAmount,
            'payment_proof_path' => $proofPath,
            'status' => $isManualPayment ? 'pending' : 'active', // Se for manual, vai para pendente validação
            'total_price' => $this->total // Atualiza o total final gravado
        ]);

        // Atualizar endereço do usuário se necessário
        if ($this->cart->user) {
            $this->cart->user->update(['delivery_address' => $this->delivery_address]);
        }

        // Incrementar uso do cupão
        if ($this->couponApplied) {
            $this->couponApplied->increment('usage_count');
        }

        return redirect()->route('whatsapp.success', ['uuid' => $this->cart->id]);
    }

    public function render()
    {
        return view('livewire.site.checkout.whatsapp-checkout')->layout('components.layouts.defaults.empty');
        // 'components.layouts.empty' deve ser um layout HTML simples sem header/footer do site principal
    }
}
