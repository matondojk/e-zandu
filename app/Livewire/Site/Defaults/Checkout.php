<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ShippingRate;
use App\Models\PaymentMethod;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Support\Str;

class Checkout extends Component
{
    public $cartItems = [];
    public $subtotal = 0;
    public $discountAmount = 0;
    public $total = 0;
    public $couponApplied = null;
    
    public $couponCodeInput = ''; 

    public $authMode = 'login';
    public $loginInput = ''; 
    public $loginPassword = ''; 
    public $registerName = '';
    public $registerEmail = '';
    public $registerPhone = '';
    public $registerPassword = '';
    
    public $shippingRates = [];
    public $paymentMethods = [];
    public $selectedShippingRateId = null;
    public $selectedPaymentMethodId = null;
    public $shippingCost = 0;
    public $addressReference = '';
    public $orderNotes = '';

    public function mount()
    {
        $this->cartItems = Session::get('cart', []);
        
        if (empty($this->cartItems)) {
            return redirect()->route('shop');
        }

        $this->couponApplied = Session::get('coupon');
        $this->shippingRates = ShippingRate::where('is_active', true)->get();
        $this->paymentMethods = PaymentMethod::where('is_active', true)->get();
        
        if (Auth::check()) {
            $this->addressReference = Auth::user()->delivery_address;
        }

        $this->calculateTotals();
    }

    public function applyCoupon()
    {
        $this->validate(['couponCodeInput' => 'required'], ['couponCodeInput.required' => 'Digite o código.']);

        $coupon = Coupon::where('code', $this->couponCodeInput)->first();

        if (!$coupon) {
            $this->addError('couponCodeInput', 'Cupão inválido.');
            return;
        }
        if (!$coupon->is_active) {
            $this->addError('couponCodeInput', 'Cupão inativo.');
            return;
        }
        if ($coupon->expires_at && $coupon->expires_at < now()) {
            $this->addError('couponCodeInput', 'Cupão expirado.');
            return;
        }
        if ($coupon->usage_limit > 0 && $coupon->usage_count >= $coupon->usage_limit) {
             $this->addError('couponCodeInput', 'Limite de uso atingido.');
             return;
        }

        $couponData = [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ];

        Session::put('coupon', $couponData);
        $this->couponApplied = $couponData;
        $this->couponCodeInput = '';
        
        $this->calculateTotals();
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Desconto aplicado!']);
    }

    public function removeCoupon()
    {
        Session::forget('coupon');
        $this->couponApplied = null;
        $this->calculateTotals();
        $this->dispatch('toast', ['type' => 'info', 'message' => 'Cupão removido.']);
    }

    public function updatedSelectedShippingRateId($value)
    {
        $rate = $this->shippingRates->find($value);
        $this->shippingCost = $rate ? $rate->rate : 0;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->cartItems as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }

        $this->discountAmount = 0;
        if ($this->couponApplied) {
            if ($this->couponApplied['type'] === 'percentage') {
                $this->discountAmount = $this->subtotal * ($this->couponApplied['value'] / 100);
            } else {
                $this->discountAmount = $this->couponApplied['value'];
            }
        }
        
        $this->discountAmount = min($this->discountAmount, $this->subtotal);
        $this->total = ($this->subtotal - $this->discountAmount) + $this->shippingCost;
    }

    public function loginUser()
    {
        $this->validate([
            'loginInput' => 'required',
            'loginPassword' => 'required'
        ]);

        $fieldType = filter_var($this->loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        
        if (Auth::attempt([$fieldType => $this->loginInput, 'password' => $this->loginPassword])) {
            $this->dispatch('toast', ['type' => 'success', 'message' => 'Login realizado!']);
            $this->addressReference = Auth::user()->delivery_address;
        } else {
            $this->addError('loginInput', 'Credenciais inválidas.');
        }
    }

    public function registerUser()
    {
        $this->validate([
            'registerName' => 'required|min:3',
            'registerEmail' => 'required|email|unique:users,email',
            'registerPhone' => 'required|unique:users,phone',
            'registerPassword' => 'required|min:6',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $this->registerName,
            'email' => $this->registerEmail,
            'phone' => $this->registerPhone,
            'password' => Hash::make($this->registerPassword),
            'role' => 'customer',
            'delivery_address' => null 
        ]);

        Auth::login($user);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Conta criada!']);
    }

    public function placeOrder()
    {
        if (!Auth::check()) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Sessão expirou. Faça login.']);
            return;
        }

        $this->validate([
            'selectedShippingRateId' => 'required',
            'addressReference' => 'required',
            'selectedPaymentMethodId' => 'required',
        ]);

        try {
            $cart = DB::transaction(function () {
                Auth::user()->update(['delivery_address' => $this->addressReference]);

                $newCart = Cart::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => Auth::id(),
                    'status' => 'active',
                    'total_price' => $this->subtotal, 
                    'shipping_rate_id' => $this->selectedShippingRateId,
                    'payment_method_id' => $this->selectedPaymentMethodId,
                    'coupon_id' => $this->couponApplied['id'] ?? null,
                    'discount_amount' => $this->discountAmount,
                ]);

                foreach ($this->cartItems as $item) {
                    $newCart->items()->create([
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price']
                    ]);
                }

                if ($this->couponApplied) {
                    Coupon::where('id', $this->couponApplied['id'])->increment('usage_count');
                }

                return $newCart;
            });

            Session::forget('cart');
            Session::forget('coupon');
            $this->dispatch('cart-updated');

            $paymentMethod = $this->paymentMethods->find($this->selectedPaymentMethodId);

            if ($paymentMethod->type === 'cash_on_delivery') {
                return redirect()->route('order.success', ['cartId' => $cart->id]);
            } else {
                return redirect()->route('payment.upload', ['cartId' => $cart->id]);
            }

        } catch (\Exception $e) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Erro: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.site.defaults.checkout')->layout('components.layouts.defaults.app', [
            'title' => 'Checkout | Zandu'
        ]);
    }
}