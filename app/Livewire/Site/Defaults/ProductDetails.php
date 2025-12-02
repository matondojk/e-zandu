<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class ProductDetails extends Component
{
    public $slug;
    public $product;
    
    // Estado da Galeria
    public $currentImage;

    // Formulário de Review
    public $rating = 5;
    public $comment = '';

    public function mount($slug)
    {
        // Carrega o produto com relacionamentos necessários
        // Adicionado 'crossSellProducts' ao eager loading
        $this->product = Product::with([
            'images', 
            'categories', 
            'brands',
            'crossSellProducts', // ✅ Carrega os produtos de cross-sell
            'reviews' => function($query) {
                $query->where('status', 'approved')->latest();
            },
            'reviews.user'
        ])->where('slug', $slug)->firstOrFail();

        $this->slug = $slug;
        $this->currentImage = $this->product->image_url;
    }

    // Método para trocar a imagem principal ao clicar na miniatura
    public function setImage($url)
    {
        $this->currentImage = $url;
    }

    public function saveReview()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:500',
        ], [
            'comment.required' => 'Por favor, escreva um comentário.',
            'comment.min' => 'O comentário deve ter pelo menos 5 caracteres.'
        ]);

        $this->product->reviews()->create([
            'user_id' => Auth::id(),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => 'pending'
        ]);

        $this->reset(['rating', 'comment']);
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Avaliação enviada para aprovação!']);
    }

    // Adicionar ao carrinho (Suporta adicionar produtos relacionados também)
    public function addToCart($productId = null)
    {
        // Se nenhum ID for passado, usa o produto atual da página
        $productToAdd = $productId ? Product::find($productId) : $this->product;

        if (!$productToAdd) return;

        // Verifica se tem preço promocional ativo
        $finalPrice = ($productToAdd->promotional_price && $productToAdd->promotional_price < $productToAdd->price) 
            ? $productToAdd->promotional_price 
            : $productToAdd->price;

        $cart = Session::get('cart', []);
        $id = $productToAdd->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'id' => $id,
                'name' => $productToAdd->name,
                'price' => $finalPrice,
                'image' => $productToAdd->image_url,
                'quantity' => 1,
            ];
        }

        Session::put('cart', $cart);
        $this->dispatch('cart-updated'); 
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Adicionado ao carrinho!']);
    }

    public function render()
    {
        $avgRating = $this->product->reviews->avg('rating') ?? 0;
        $reviewsCount = $this->product->reviews->count();
        
        $discountPercent = 0;
        if($this->product->promotional_price && $this->product->promotional_price < $this->product->price) {
            $discountPercent = round((($this->product->price - $this->product->promotional_price) / $this->product->price) * 100);
        }

        // ✅ Buscar Produtos Relacionados
        // Mesma Categoria OU Mesma Marca, excluindo o próprio produto
        $relatedProducts = Product::where('id', '!=', $this->product->id)
            ->where(function($query) {
                $query->whereHas('categories', function($q) {
                    $q->whereIn('categories.id', $this->product->categories->pluck('id'));
                })
                ->orWhereHas('brands', function($q) {
                    $q->whereIn('brands.id', $this->product->brands->pluck('id'));
                });
            })
            ->inRandomOrder()
            ->take(4)
            ->get();

        return view('livewire.site.defaults.product-details', [
            'avgRating' => $avgRating,
            'reviewsCount' => $reviewsCount,
            'discountPercent' => $discountPercent,
            'relatedProducts' => $relatedProducts, // ✅ Passando para a view
            'crossSellProducts' => $this->product->crossSellProducts // ✅ Passando Cross-Sell
        ])->layout('components.layouts.defaults.app', [
            'title' => $this->product->name . ' | Zandu',
            'description' => $this->product->short_description,
            'image' => $this->product->image_url
        ]);
    }
}