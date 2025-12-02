<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Coupon; // Baseado no teu CouponSeeder

class Cart extends Component
{
    public $cartItems = [];
    public $couponCode = '';
    public $discountAmount = 0;
    public $subtotal = 0;
    public $total = 0;

    public function mount()
    {
        $this->refreshCart();
    }

    // Atualiza as variáveis locais baseadas na sessão
    public function refreshCart()
    {
        $this->cartItems = Session::get('cart', []);
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->cartItems as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }

        // Lógica de Cupão
        $this->discountAmount = 0;
        $couponSession = Session::get('coupon');

        if ($couponSession) {
            if ($couponSession['type'] === 'percentage') {
                $this->discountAmount = $this->subtotal * ($couponSession['value'] / 100);
            } elseif ($couponSession['type'] === 'fixed') {
                $this->discountAmount = $couponSession['value'];
            }
        }

        // Garante que o desconto não é maior que o subtotal
        if ($this->discountAmount > $this->subtotal) {
            $this->discountAmount = $this->subtotal;
        }

        $this->total = $this->subtotal - $this->discountAmount;
    }

    // Incrementar/Decrementar Quantidade
    public function updateQuantity($id, $change)
    {
        $cart = Session::get('cart', []);

        if (isset($cart[$id])) {
            $newQuantity = $cart[$id]['quantity'] + $change;

            if ($newQuantity > 0) {
                $cart[$id]['quantity'] = $newQuantity;
            } else {
                // Se chegar a zero, removemos ou mantemos 1? Geralmente remove-se ou mantém 1.
                // Vamos manter 1 para evitar remoção acidental, remoção explícita no botão lixo.
                $cart[$id]['quantity'] = 1; 
            }
            
            Session::put('cart', $cart);
            $this->dispatch('cart-updated'); // Atualiza o header
            $this->refreshCart();
        }
    }

    public function removeItem($id)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            Session::put('cart', $cart);
            
            // Se o carrinho ficar vazio, removemos o cupão também
            if (empty($cart)) {
                Session::forget('coupon');
            }

            $this->dispatch('cart-updated');
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Item removido do carrinho.']);
            $this->refreshCart();
        }
    }

    public function applyCoupon()
    {
        $this->validate(['couponCode' => 'required']);

        // Baseado no CouponSeeder: campos code, is_active, expires_at
        $coupon = Coupon::where('code', $this->couponCode)->first();

        if (!$coupon) {
            $this->addError('couponCode', 'Cupão inválido.');
            return;
        }

        if (!$coupon->is_active) {
            $this->addError('couponCode', 'Este cupão foi desativado.');
            return;
        }

        if ($coupon->expires_at && $coupon->expires_at < now()) {
            $this->addError('couponCode', 'Este cupão expirou.');
            return;
        }

        // Guardar na sessão (apenas 1 cupão por vez, sobrescreve o anterior)
        Session::put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type, // 'percentage' ou 'fixed'
            'value' => $coupon->value,
        ]);

        $this->couponCode = ''; // Limpar input
        $this->refreshCart();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Cupão aplicado com sucesso!']);
    }

    public function removeCoupon()
    {
        Session::forget('coupon');
        $this->refreshCart();
        $this->dispatch('toast', ['type' => 'info', 'message' => 'Cupão removido.']);
    }

    public function render()
    {
        return view('livewire.site.defaults.cart')->layout('components.layouts.defaults.app', [
            'title' => 'Meu Carrinho | Zandu',
        ]);
    }
}