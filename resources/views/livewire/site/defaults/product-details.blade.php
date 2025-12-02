<div class="bg-white min-h-screen pb-20" x-data="{ 
    zoom: false,
    activeTab: 'desc',
    scrollCarousel(ref, direction) {
        const container = this.$refs[ref];
        const scrollAmount = container.clientWidth * 0.8;
        if (direction === 'left') {
            container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        } else {
            container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }
}">
    
    <div class="relative bg-gray-900 py-12 border-b border-gray-800 overflow-hidden">
        <div class="absolute inset-0 opacity-20 mix-blend-overlay">
             <img src="https://images.unsplash.com/photo-1550009158-9ebf69056955?q=80&w=2073&auto=format&fit=crop" alt="Background" class="w-full h-full object-cover">
        </div>
        <div class="absolute top-[-50%] right-[-10%] w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="relative container mx-auto px-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 bg-white/10 rounded-lg flex items-center justify-center text-zandu-red backdrop-blur-sm border border-white/10">
                            <i class="ph-bold ph-cube"></i>
                        </div>
                        <span class="text-zandu-red font-bold tracking-wider text-xs uppercase">Loja Oficial</span>
                    </div>
                    <h1 class="text-2xl md:text-4xl font-extrabold text-white tracking-tight">
                        {{ $product->name }}
                    </h1>
                </div>
                
                <nav class="flex text-xs font-bold text-gray-400 bg-white/5 px-5 py-2.5 rounded-full backdrop-blur-md border border-white/10 w-fit">
                    <a href="{{ route('home') }}" class="hover:text-white transition flex items-center gap-1"><i class="ph-bold ph-house"></i> Início</a>
                    <span class="mx-3 text-gray-600">/</span>
                    <a href="{{ route('shop') }}" class="hover:text-white transition">Loja</a>
                    <span class="mx-3 text-gray-600">/</span>
                    <span class="text-white truncate max-w-[100px]">{{ $product->name }}</span>
                </nav>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            
            <div class="lg:col-span-7 space-y-6">
                <div class="relative aspect-square md:aspect-[4/3] bg-gray-50 rounded-3xl overflow-hidden border border-gray-100 group cursor-zoom-in"
                     @mousemove="zoom = true" 
                     @mouseleave="zoom = false">
                    
                    @if($discountPercent > 0)
                        <span class="absolute top-6 left-6 bg-gray-900 text-white text-xs font-bold px-4 py-2 rounded-full z-20 shadow-lg">
                            Economize {{ $discountPercent }}%
                        </span>
                    @endif
                    
                    <img src="{{ $currentImage }}" 
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-contain p-10 transition-transform duration-500 mix-blend-multiply"
                         :class="{ 'scale-150': zoom, 'scale-100': !zoom }">
                    
                    <div class="absolute bottom-6 right-6 bg-white/80 backdrop-blur p-2 rounded-xl text-xs font-bold text-gray-500 pointer-events-none flex items-center gap-2">
                        <i class="ph-bold ph-magnifying-glass-plus"></i> Passe o mouse para ampliar
                    </div>
                </div>

                @if($product->images->count() > 0)
                    <div class="flex gap-4 overflow-x-auto pb-2 custom-scrollbar snap-x">
                        <button wire:click="setImage('{{ $product->image_url }}')" 
                                class="w-24 h-24 flex-shrink-0 rounded-2xl border-2 {{ $currentImage === $product->image_url ? 'border-gray-900 ring-2 ring-gray-200' : 'border-gray-100 hover:border-gray-300' }} bg-gray-50 p-2 transition snap-start">
                            <img src="{{ $product->image_url }}" class="w-full h-full object-contain mix-blend-multiply">
                        </button>

                        @foreach($product->images as $img)
                            <button wire:click="setImage('{{ $img->url }}')" 
                                    class="w-24 h-24 flex-shrink-0 rounded-2xl border-2 {{ $currentImage === $img->url ? 'border-gray-900 ring-2 ring-gray-200' : 'border-gray-100 hover:border-gray-300' }} bg-gray-50 p-2 transition snap-start">
                                <img src="{{ $img->url }}" class="w-full h-full object-contain mix-blend-multiply">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5 flex flex-col h-full">
                <div class="sticky top-24">
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            @if($product->brands->isNotEmpty())
                                <span class="text-xs font-bold bg-gray-100 text-gray-600 px-3 py-1 rounded-full uppercase tracking-wider">
                                    {{ $product->brands->first()->name }}
                                </span>
                            @endif
                            
                            <div class="flex items-center gap-1 text-yellow-400 text-sm">
                                <span class="font-bold text-gray-900 mr-1">{{ number_format($avgRating, 1) }}</span>
                                @for($i=1; $i<=5; $i++)
                                    <i class="ph-fill {{ $i <= round($avgRating) ? 'ph-star' : 'ph-star text-gray-200' }}"></i>
                                @endfor
                                <span class="text-xs text-gray-400 ml-1 underline cursor-pointer" @click="document.getElementById('reviews-section').scrollIntoView({behavior: 'smooth'})">
                                    ({{ $reviewsCount }} avaliações)
                                </span>
                            </div>
                        </div>

                        <h2 class="text-3xl font-bold text-gray-900 mb-4 leading-tight">{{ $product->name }}</h2>
                        
                        <p class="text-gray-600 text-base leading-relaxed mb-6">
                            {{ $product->short_description }}
                        </p>

                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                            <div class="mb-6">
                                @if($product->promotional_price && $product->promotional_price < $product->price)
                                    <div class="flex flex-col">
                                        <span class="text-sm text-gray-400 line-through mb-1">
                                            {{ number_format($product->price, 0, ',', '.') }} Kz
                                        </span>
                                        <div class="flex items-center gap-3">
                                            <span class="text-4xl font-extrabold text-gray-900">
                                                {{ number_format($product->promotional_price, 0, ',', '.') }} <span class="text-lg text-gray-500 font-bold">Kz</span>
                                            </span>
                                            <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded-md">Oferta</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-4xl font-extrabold text-gray-900">
                                        {{ number_format($product->price, 0, ',', '.') }} <span class="text-lg text-gray-500 font-bold">Kz</span>
                                    </span>
                                @endif
                            </div>

                            <div class="flex gap-4 mb-6">
                                <div class="w-24">
                                    <div class="relative">
                                        <input type="number" value="1" min="1" class="w-full bg-white border border-gray-200 rounded-xl py-3.5 px-4 font-bold text-center focus:outline-none focus:border-gray-900" readonly>
                                    </div>
                                </div>
                                <button 
                                    wire:click="addToCart" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 bg-gray-900 text-white font-bold text-lg py-3.5 rounded-xl hover:bg-zandu-red transition shadow-xl hover:shadow-red-900/20 transform active:scale-95 flex items-center justify-center gap-3 disabled:opacity-70"
                                >
                                    <span wire:loading.remove class="flex items-center gap-2">
                                        Adicionar ao Carrinho <i class="ph-bold ph-shopping-cart"></i>
                                    </span>
                                    <span wire:loading><i class="ph ph-spinner animate-spin text-2xl"></i> Processando...</span>
                                </button>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3 text-xs font-medium text-gray-500">
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-truck text-lg text-zandu-red"></i> Entrega em 24h
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-shield-check text-lg text-green-600"></i> Garantia Oficial
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-credit-card text-lg text-blue-600"></i> Pagamento Seguro
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-arrow-u-up-left text-lg text-orange-500"></i> Devolução Grátis
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($crossSellProducts && $crossSellProducts->count() > 0)
            <div class="mt-24 border-t border-gray-200 pt-12 relative group/carousel">
                <h3 class="text-2xl font-bold text-gray-900 mb-8 flex items-center gap-2">
                    <i class="ph-fill ph-shopping-cart text-zandu-red"></i> Frequentemente comprados juntos
                </h3>
                
                <button @click="scrollCarousel('crossSell', 'left')" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white border border-gray-200 text-gray-700 w-10 h-10 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition hover:bg-gray-50 hover:scale-110">
                    <i class="ph-bold ph-caret-left"></i>
                </button>
                <button @click="scrollCarousel('crossSell', 'right')" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white border border-gray-200 text-gray-700 w-10 h-10 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition hover:bg-gray-50 hover:scale-110">
                    <i class="ph-bold ph-caret-right"></i>
                </button>

                <div x-ref="crossSell" class="flex gap-6 overflow-x-auto hide-scrollbar snap-x snap-mandatory scroll-smooth py-4 px-1">
                    @foreach($crossSellProducts as $crossItem)
                        <div class="snap-start flex-shrink-0 w-[280px] bg-white border border-gray-100 rounded-2xl p-4 hover:shadow-xl transition flex flex-col relative group">
                            <div class="h-40 bg-gray-50 rounded-xl mb-4 p-4 flex items-center justify-center overflow-hidden">
                                <img src="{{ $crossItem->image_url }}" class="w-full h-full object-contain mix-blend-multiply transform group-hover:scale-110 transition duration-500">
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2 h-10">
                                <a href="{{ route('product.details', $crossItem->slug) }}" class="hover:text-zandu-red transition">{{ $crossItem->name }}</a>
                            </h4>
                            <div class="mt-auto flex items-center justify-between pt-2">
                                <span class="font-extrabold text-gray-900">{{ number_format($crossItem->price, 0, ',', '.') }} Kz</span>
                                <button wire:click="addToCart('{{ $crossItem->id }}')" class="w-8 h-8 bg-gray-900 text-white rounded-lg flex items-center justify-center hover:bg-zandu-red transition shadow-md">
                                    <i class="ph-bold ph-plus"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="reviews-section" class="mt-24" x-data="{ tab: 'desc' }">
            <div class="flex border-b border-gray-200 mb-8 overflow-x-auto">
                <button 
                    @click="tab = 'desc'" 
                    :class="tab === 'desc' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-8 py-4 text-lg font-bold border-b-4 transition whitespace-nowrap"
                >
                    Descrição
                </button>
                <button 
                    @click="tab = 'reviews'" 
                    :class="tab === 'reviews' ? 'border-gray-900 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="px-8 py-4 text-lg font-bold border-b-4 transition whitespace-nowrap flex items-center gap-2"
                >
                    Avaliações <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">{{ $reviewsCount }}</span>
                </button>
            </div>

            <div x-show="tab === 'desc'" x-transition.opacity class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm prose prose-lg max-w-none text-gray-600">
                <p>{{ $product->description }}</p>
            </div>

            <div x-show="tab === 'reviews'" x-transition.opacity style="display: none;">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                    <div class="lg:col-span-2 space-y-6">
                        @forelse($product->reviews as $review)
                            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                                <div class="flex items-start gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center text-gray-600 font-bold flex-shrink-0">
                                        {{ substr($review->user->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <h4 class="font-bold text-gray-900">{{ $review->user->name ?? 'Anônimo' }}</h4>
                                            <span class="text-xs text-gray-400">{{ $review->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex text-yellow-400 text-xs mb-2">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="ph-fill {{ $i <= $review->rating ? 'ph-star' : 'ph-star text-gray-200' }}"></i>
                                            @endfor
                                        </div>
                                        <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="bg-gray-50 rounded-2xl p-12 text-center border border-dashed border-gray-200">
                                <i class="ph-duotone ph-star text-4xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500 font-medium">Este produto ainda não tem avaliações.</p>
                                <p class="text-sm text-gray-400">Seja o primeiro a avaliar!</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-gray-900 text-white rounded-2xl p-8 sticky top-24 shadow-xl">
                            <h3 class="text-xl font-bold mb-6 flex items-center gap-2">
                                <i class="ph-bold ph-pencil-simple"></i> Escrever Avaliação
                            </h3>
                            @auth
                                <form wire:submit.prevent="saveReview">
                                    <div class="mb-6">
                                        <label class="block text-sm font-bold text-gray-300 mb-3">Sua Nota</label>
                                        <div class="flex gap-2">
                                            @for($i=1; $i<=5; $i++)
                                                <button type="button" wire:click="$set('rating', {{ $i }})" class="focus:outline-none transition transform hover:scale-110">
                                                    <i class="ph-fill ph-star text-2xl {{ $rating >= $i ? 'text-yellow-400' : 'text-gray-600' }}"></i>
                                                </button>
                                            @endfor
                                        </div>
                                        @error('rating') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="mb-6">
                                        <label class="block text-sm font-bold text-gray-300 mb-3">Comentário</label>
                                        <textarea wire:model="comment" rows="4" class="w-full bg-gray-800 border border-gray-700 rounded-xl p-4 text-white focus:outline-none focus:border-white transition resize-none text-sm" placeholder="O que você achou?"></textarea>
                                        @error('comment') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                    <button type="submit" class="w-full bg-white text-gray-900 font-bold py-3.5 rounded-xl hover:bg-gray-200 transition shadow-lg">
                                        Publicar Opinião
                                    </button>
                                </form>
                            @else
                                <div class="text-center py-4">
                                    <p class="text-gray-400 mb-6">Faça login para compartilhar sua experiência.</p>
                                    <a href="{{ route('login') }}" class="block w-full bg-white text-gray-900 font-bold py-3 rounded-xl hover:bg-gray-100 transition text-center">
                                        Entrar na Conta
                                    </a>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($relatedProducts->isNotEmpty())
            <div class="mt-24 border-t border-gray-200 pt-12 relative group/carousel">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Produtos Relacionados</h2>
                        <p class="text-gray-500 text-sm mt-1">Você também pode gostar.</p>
                    </div>
                    <a href="{{ route('shop') }}" class="hidden md:flex items-center gap-2 text-gray-900 font-bold hover:underline">
                        Ver Loja <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>

                <button @click="scrollCarousel('related', 'left')" class="absolute left-0 top-1/2 z-10 bg-white border border-gray-200 text-gray-700 w-12 h-12 rounded-full shadow-xl flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition hover:scale-110 -translate-x-4">
                    <i class="ph-bold ph-caret-left text-xl"></i>
                </button>
                <button @click="scrollCarousel('related', 'right')" class="absolute right-0 top-1/2 z-10 bg-white border border-gray-200 text-gray-700 w-12 h-12 rounded-full shadow-xl flex items-center justify-center opacity-0 group-hover/carousel:opacity-100 transition hover:scale-110 translate-x-4">
                    <i class="ph-bold ph-caret-right text-xl"></i>
                </button>

                <div x-ref="related" class="flex gap-6 overflow-x-auto hide-scrollbar snap-x snap-mandatory scroll-smooth py-4 px-1">
                    @foreach($relatedProducts as $relProduct)
                        <div class="snap-start flex-shrink-0 w-full sm:w-1/2 lg:w-1/4 group bg-white rounded-3xl border border-gray-100 p-4 hover:shadow-2xl transition-all duration-300 relative">
                            @if($relProduct->promotional_price && $relProduct->promotional_price < $relProduct->price)
                                <span class="absolute top-4 left-4 bg-zandu-red text-white text-[10px] font-bold px-2 py-1 rounded-md z-10">Promo</span>
                            @endif

                            <div class="relative h-64 bg-gray-50 rounded-2xl mb-4 flex items-center justify-center overflow-hidden cursor-pointer">
                                <a href="{{ route('product.details', $relProduct->slug) }}" class="absolute inset-0 z-20"></a>
                                <img src="{{ $relProduct->image_url }}" class="w-full h-full object-contain p-6 transform group-hover:scale-110 transition duration-500 mix-blend-multiply">
                            </div>

                            <div class="px-2">
                                <h3 class="font-bold text-gray-900 text-lg mb-1 line-clamp-1 hover:text-zandu-red transition">
                                    <a href="{{ route('product.details', $relProduct->slug) }}">{{ $relProduct->name }}</a>
                                </h3>
                                <div class="flex items-end justify-between mt-2">
                                    <div>
                                        @if($relProduct->promotional_price && $relProduct->promotional_price < $relProduct->price)
                                            <p class="text-xs text-gray-400 line-through">{{ number_format($relProduct->price, 0, ',', '.') }} Kz</p>
                                            <p class="text-lg font-extrabold text-gray-900">{{ number_format($relProduct->promotional_price, 0, ',', '.') }} Kz</p>
                                        @else
                                            <p class="text-lg font-extrabold text-gray-900">{{ number_format($relProduct->price, 0, ',', '.') }} Kz</p>
                                        @endif
                                    </div>
                                    <button wire:click="addToCart('{{ $relProduct->id }}')" class="w-10 h-10 rounded-full bg-gray-900 text-white flex items-center justify-center hover:bg-zandu-red transition shadow-lg">
                                        <i class="ph-bold ph-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <style>
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</div>