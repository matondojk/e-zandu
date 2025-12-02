<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Session;

class Shop extends Component
{
    use WithPagination;

    // Mantém os filtros na URL (ex: ?search=iphone&sort=price_asc)
    #[Url(history: true)] 
    public $search = '';

    #[Url] 
    public $selectedCategories = [];

    #[Url] 
    public $selectedBrands = [];

    #[Url] 
    public $sort = 'newest';

    #[Url]
    public $minPrice = 0;

    #[Url]
    public $maxPrice = 2000000; // 2 Milhões KZ (Ajustável)

    // Resetar paginação quando filtrar
    public function updatedSearch() { $this->resetPage(); }
    public function updatedSelectedCategories() { $this->resetPage(); }
    public function updatedSelectedBrands() { $this->resetPage(); }
    public function updatedSort() { $this->resetPage(); }

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
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Adicionado ao carrinho']);
    }

    public function render()
    {
        $query = Product::query();

        // 1. Busca por Texto
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('short_description', 'like', '%' . $this->search . '%');
        }

        // 2. Filtro de Categorias
        if (!empty($this->selectedCategories)) {
            $query->whereHas('categories', function ($q) {
                $q->whereIn('id', $this->selectedCategories);
            });
        }

        // 3. Filtro de Marcas
        if (!empty($this->selectedBrands)) {
            $query->whereHas('brands', function ($q) {
                $q->whereIn('id', $this->selectedBrands);
            });
        }

        // 4. Filtro de Preço
        $query->whereBetween('price', [$this->minPrice, $this->maxPrice]);

        // 5. Ordenação
        switch ($this->sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Carregar dados auxiliares para o Sidebar
        $categories = Category::withCount('products')->get();
        $brands = Brand::withCount('products')->get();

        // Processar produtos para view (rating e desconto)
        $products = $query->with('reviews')->paginate(12); // 12 por página
        
        $products->getCollection()->transform(function($product) {
            $product->avg_rating = $product->reviews->avg('rating') ?? 0;
            $product->reviews_count = $product->reviews->count();
            $product->discount_percent = 0;
            
            if($product->promotional_price && $product->promotional_price < $product->price) {
                $product->discount_percent = round((($product->price - $product->promotional_price) / $product->price) * 100);
            }
            return $product;
        });

        return view('livewire.site.defaults.shop', [
            'products' => $products,
            'allCategories' => $categories,
            'allBrands' => $brands
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Loja Zandu - Todos os Produtos',
            'description' => 'Explore nossa coleção completa de tecnologia.'
        ]);
    }
}