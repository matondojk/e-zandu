<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class OrderSuccess extends Component
{
    public $cart;

    public function mount($cartId)
    {
        $this->cart = Cart::where('id', $cartId)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.site.defaults.order-success')->layout('components.layouts.defaults.app', [
            'title' => 'Pedido Confirmado | Zandu'
        ]);
    }
}