<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class HomePage extends Component
{
    public $categories;
    public $products;

    public function mount()
    {
        $this->categories = Category::latest()->take(6)->get();
        $this->products = Product::latest()->take(8)->get();
    }

    /**
     * Lógica completa de adicionar ao carrinho (baseado em Sessão)
     */
    public function addToCart($productId)
    {
        // 1. Encontrar o produto
        $product = Product::find($productId);
        if (!$product) {
            return; // Ou lidar com o erro
        }

        // 2. Pegar o carrinho da sessão (ou um array vazio se não existir)
        $cart = session()->get('cart', []);

        // 3. Verificar se o produto JÁ ESTÁ no carrinho
        if (isset($cart[$productId])) {
            
            // Se sim, apenas incrementa a quantidade
            $cart[$productId]['quantity']++;

        } else {
            
            // Se não, adiciona o produto ao carrinho
            $cart[$productId] = [
                "id" => $product->id,
                "name" => $product->name,
                "quantity" => 1,
                "price" => $product->price,
                "image" => $product->image_url,
                "slug" => $product->slug // Adiciona o slug para links fáceis
            ];
        }

        // 4. Salvar o carrinho de volta na sessão
        session()->put('cart', $cart);

        // 5. Enviar feedback e disparar evento (para o mini-carrinho)
        session()->flash('message', "{$product->name} foi adicionado ao carrinho!");
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.pages.home-page');
    }
}