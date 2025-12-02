<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Session;

class Home extends Component
{
    public function addToCart($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->dispatch('toast', ['type' => 'error', 'message' => 'Erro ao adicionar produto.']);
            return;
        }

        $finalPrice = ($product->promotional_price && $product->promotional_price < $product->price) 
            ? $product->promotional_price 
            : $product->price;

        $cart = Session::get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity']++;
        } else {
            $cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $finalPrice,
                'image' => $product->image_url,
                'quantity' => 1,
            ];
        }

        Session::put('cart', $cart);

        $this->dispatch('cart-updated'); 
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Produto adicionado ao carrinho!']);
    }

    public function render()
    {
        $categories = Category::whereNotNull('icon')->take(6)->get();

        $featuredProducts = Product::with('reviews')
            ->inRandomOrder()
            ->take(8)
            ->get()
            ->map(function($product) {
                $product->avg_rating = $product->reviews->avg('rating') ?? 0;
                $product->reviews_count = $product->reviews->count();
                
                $product->discount_percent = 0;
                if($product->promotional_price && $product->promotional_price < $product->price) {
                    $product->discount_percent = round((($product->price - $product->promotional_price) / $product->price) * 100);
                }
                return $product;
            });

        return view('livewire.site.defaults.home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Zandu - A Melhor Loja de Tecnologia',
            'description' => 'Encontre Smartphones, Laptops e Acessórios com os melhores preços de Angola.',
            'image' => 'https://e-zandu.com/default/logo-dark.png', 
        ]);
    }
}