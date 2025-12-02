<?php

namespace App\Livewire\Site\Order;

use Livewire\Component;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class SiteOrderSuccess extends Component
{
    public $cart;
    public $relatedProducts;

    public function mount($uuid)
    {
        // CORREÇÃO AQUI:
        // Se no seu migration o UUID é a chave primária 'id', use 'id'.
        // Se tem uma coluna separada chamada 'uuid', mantenha 'uuid'.
        // Baseado no seu código anterior (Checkout), você salva o UUID no 'id'.
        
        $this->cart = Cart::with(['items.product', 'paymentMethod', 'shippingRate', 'user'])
            ->where('id', $uuid) // <-- Mudado de 'uuid' para 'id'
            ->firstOrFail(); 
    }

    public function render()
    {
        return view('livewire.site.order.order-success')->layout('components.layouts.defaults.app', [
            'title' => 'Pedido Confirmado | Zandu'
        ]);
    }
}