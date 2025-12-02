<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Livewire\Attributes\On;

class Header extends Component
{
    public $cartCount = 0;

    public function mount()
    {
        $this->updateCartCount();
    }

    #[On('cart-updated')]
    public function updateCartCount()
    {
        // Recupera o carrinho da sessão (simples array por enquanto)
        $cart = session()->get('cart', []);
        $this->cartCount = count($cart);
    }

    public function render()
    {
        return view('livewire.site.defaults.header');
    }
}