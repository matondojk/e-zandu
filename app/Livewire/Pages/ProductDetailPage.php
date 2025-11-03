<?php

namespace App\Livewire\Pages;

use App\Models\Product;
use App\Models\ProductImage;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Wishlist;
use App\Models\ProductReview;

#[Layout('layouts.app')]
class ProductDetailPage extends Component
{
    public Product $product;
    public $gallery = [];
    public $activeImage;
    
    public $quantity = 1;

    public $crossSellProducts = [];
    public $reviews = [];
    public $averageRating = 0;
    public $inWishlist = false;

    // Para o formulário de avaliação
    public $newRating = 5;
    public $newComment = '';

    /**
     * O método 'mount' é executado quando o componente é carregado.
     * Ele recebe o 'slug' da URL.
     */
    public function mount(string $slug)
    {
        // Carrega o produto com suas relações
        $this->product = Product::where('slug', $slug)
                            ->with(['images', 'approvedReviews.user', 'crossSellProducts'])
                            ->firstOrFail();

        // 1. Monta a Galeria
        // Adiciona a imagem principal
        $this->gallery[] = ['url' => $this->product->image_url, 'id' => 'main'];
        // Adiciona as imagens da galeria (da tabela product_images)
        foreach ($this->product->images as $image) {
            $this->gallery[] = ['url' => $image->url, 'id' => $image->id];
        }
        // Define a imagem principal como ativa
        $this->activeImage = $this->gallery[0]['url'];

        // 2. Carrega Cross-Sell
        $this->crossSellProducts = $this->product->crossSellProducts;

        // 3. Carrega Avaliações e calcula a média
        $this->loadReviews();

        // 4. Verifica se está na Wishlist
        if (Auth::check()) {
            $this->inWishlist = Wishlist::where('user_id', auth()->id())
                                        ->where('product_id', $this->product->id)
                                        ->exists();
        }
    }

    public function loadReviews()
    {
        $this->reviews = $this->product->approvedReviews()->latest()->get();
        if ($this->reviews->count() > 0) {
            $this->averageRating = $this->reviews->avg('rating');
        }
    }

    // Altera a imagem principal da galeria
    public function changeActiveImage($url)
    {
        $this->activeImage = $url;
    }

    // Ações do Seletor de Quantidade
    public function increment()
    {
        $this->quantity++;
    }

    public function decrement()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    // Adicionar ao Carrinho (com quantidade)
    public function addToCart()
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$this->product->id])) {
            // Se já existe, soma a nova quantidade
            $cart[$this->product->id]['quantity'] += $this->quantity;
        } else {
            // Se é novo, adiciona
            $cart[$this->product->id] = [
                "id" => $this->product->id,
                "name" => $this->product->name,
                "quantity" => $this->quantity,
                "price" => $this->product->price,
                "image" => $this->product->image_url,
                "slug" => $this->product->slug
            ];
        }

        session()->put('cart', $cart);
        session()->flash('message', "{$this->product->name} (x{$this->quantity}) adicionado ao carrinho!");
        $this->dispatch('cart-updated');
    }

    // Adicionar à Lista de Desejos
    public function toggleWishlist()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true); // Redireciona para o login
        }

        if ($this->inWishlist) {
            // Remove
            Wishlist::where('user_id', auth()->id())
                    ->where('product_id', $this->product->id)
                    ->delete();
            $this->inWishlist = false;
            session()->flash('message', 'Removido da lista de desejos.');
        } else {
            // Adiciona
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product->id
            ]);
            $this->inWishlist = true;
            session()->flash('message', 'Adicionado à lista de desejos!');
        }
    }

    // Enviar nova avaliação
    public function submitReview()
    {
        if (!Auth::check()) {
            return $this->redirect(route('login'), navigate: true);
        }

        $this->validate([
            'newRating' => 'required|integer|min:1|max:5',
            'newComment' => 'nullable|string|min:10',
        ]);

        ProductReview::create([
            'product_id' => $this->product->id,
            'user_id' => auth()->id(),
            'rating' => $this->newRating,
            'comment' => $this->newComment,
            'status' => 'pending', // Sempre pendente para aprovação do admin
        ]);

        // Limpa o formulário e dá feedback
        $this->newRating = 5;
        $this->newComment = '';
        session()->flash('review_message', 'Obrigado! Sua avaliação foi enviada para aprovação.');
    }

    public function render()
    {
        return view('livewire.pages.product-detail-page');
    }
}