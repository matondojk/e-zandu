<div class="bg-gray-50 min-h-screen font-sans" x-data="{ showMobileFilters: false }">
    
    <div class="relative bg-gray-900 py-16 md:py-24 border-b border-gray-800 overflow-hidden">
        <div class="absolute top-0 right-0 w-full h-full bg-gradient-to-bl from-gray-800 to-gray-900 z-0"></div>
        <div class="absolute top-[-20%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[400px] h-[400px] bg-zandu-red/10 rounded-full blur-[80px] pointer-events-none"></div>

        <div class="relative container mx-auto px-4 text-center md:text-left z-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <div class="inline-flex items-center gap-2 mb-4 px-3 py-1 rounded-full border border-gray-700 bg-gray-800/50 backdrop-blur-sm">
                        <span class="w-2 h-2 bg-zandu-red rounded-full animate-pulse"></span>
                        <span class="text-zandu-red font-bold tracking-wider text-xs uppercase">Loja Oficial</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 tracking-tight">
                        Catálogo Completo
                    </h1>
                    <p class="text-gray-400 text-base md:text-lg max-w-xl mx-auto md:mx-0 leading-relaxed">
                        Explore nossa seleção de tecnologia de ponta. Filtre por categoria, marca ou preço e encontre o seu setup ideal.
                    </p>
                </div>
                
                <div class="hidden md:block opacity-20 transform rotate-12">
                    <i class="ph-fill ph-shopping-bag-open text-9xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-12">
        
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4 bg-white p-4 rounded-2xl border border-gray-100 shadow-sm">
            
            <button @click="showMobileFilters = true" class="md:hidden w-full bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-3 rounded-xl font-bold flex items-center justify-center gap-2 transition">
                <i class="ph-bold ph-faders"></i> Filtrar Produtos
            </button>

            <p class="text-gray-500 text-sm hidden md:flex items-center gap-2">
                <i class="ph-fill ph-squares-four text-gray-300 text-lg"></i>
                Mostrando <span class="font-bold text-gray-900">{{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }}</span> de <span class="font-bold text-gray-900">{{ $products->total() }}</span> resultados
            </p>

            <div class="flex items-center gap-3 w-full md:w-auto">
                <span class="text-sm font-medium text-gray-500 whitespace-nowrap hidden md:block">Ordenar:</span>
                <div class="relative w-full md:w-56">
                    <select wire:model.live="sort" class="appearance-none bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-zandu-red focus:border-zandu-red block w-full p-3 pr-10 outline-none cursor-pointer font-medium">
                        <option value="newest">✨ Mais Recentes</option>
                        <option value="price_asc">💲 Preço: Menor para Maior</option>
                        <option value="price_desc">💰 Preço: Maior para Menor</option>
                        <option value="name_asc">🔤 Nome (A-Z)</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="ph-bold ph-caret-down"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 relative">
            
            <aside :class="showMobileFilters ? 'fixed inset-0 z-50 bg-white overflow-y-auto p-6' : 'hidden lg:block w-full lg:w-1/4 lg:sticky lg:top-24 lg:h-fit'" class="transition-all duration-300 rounded-2xl">
                
                <div class="flex justify-between items-center mb-6 lg:hidden">
                    <h2 class="text-2xl font-bold text-gray-900">Filtros</h2>
                    <button @click="showMobileFilters = false" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 hover:text-zandu-red transition">
                        <i class="ph-bold ph-x text-xl"></i>
                    </button>
                </div>

                <div class="mb-8">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Buscar</h3>
                    <div class="relative group">
                        <input type="text" wire:model.live.debounce.500ms="search" placeholder="O que procura?" class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-zandu-red/20 focus:border-zandu-red text-sm transition font-medium text-gray-900 placeholder-gray-400">
                        <i class="ph-bold ph-magnifying-glass absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition"></i>
                    </div>
                </div>

                <div class="mb-8 border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider flex justify-between items-center">
                        Categorias
                    </h3>
                    <div class="space-y-3 max-h-64 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($allCategories as $category)
                            <label class="flex items-center gap-3 cursor-pointer group hover:bg-gray-50 p-2 rounded-lg transition -mx-2">
                                <div class="relative flex items-center justify-center w-5 h-5">
                                    <input type="checkbox" wire:model.live="selectedCategories" value="{{ $category->id }}" class="peer appearance-none w-5 h-5 border-2 border-gray-300 rounded-md checked:bg-zandu-red checked:border-zandu-red transition cursor-pointer">
                                    <i class="ph-bold ph-check absolute text-white text-xs opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                                </div>
                                <span class="text-gray-600 text-sm font-medium group-hover:text-gray-900 transition">{{ $category->name }}</span>
                                <span class="ml-auto text-[10px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full group-hover:bg-gray-200 transition">{{ $category->products_count }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mb-8 border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Preço (Kz)</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1">
                            <input type="number" wire:model.live.debounce.500ms="minPrice" placeholder="Min" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium focus:border-zandu-red focus:ring-0 outline-none text-center">
                        </div>
                        <span class="text-gray-400">-</span>
                        <div class="flex-1">
                            <input type="number" wire:model.live.debounce.500ms="maxPrice" placeholder="Max" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm font-medium focus:border-zandu-red focus:ring-0 outline-none text-center">
                        </div>
                    </div>
                    <input type="range" min="0" max="2000000" step="5000" wire:model.live.debounce.300ms="maxPrice" class="w-full h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-zandu-red">
                </div>

                <div class="mb-8 border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wider">Marcas</h3>
                    <div class="space-y-3 max-h-60 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($allBrands as $brand)
                            <label class="flex items-center gap-3 cursor-pointer group hover:bg-gray-50 p-2 rounded-lg transition -mx-2">
                                <div class="relative flex items-center justify-center w-5 h-5">
                                    <input type="checkbox" wire:model.live="selectedBrands" value="{{ $brand->id }}" class="peer appearance-none w-5 h-5 border-2 border-gray-300 rounded-md checked:bg-gray-900 checked:border-gray-900 transition cursor-pointer">
                                    <i class="ph-bold ph-check absolute text-white text-xs opacity-0 peer-checked:opacity-100 pointer-events-none"></i>
                                </div>
                                <span class="text-gray-600 text-sm font-medium group-hover:text-gray-900 transition">{{ $brand->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button @click="showMobileFilters = false" class="w-full bg-zandu-red text-white font-bold py-4 rounded-xl lg:hidden mt-4 shadow-lg">
                    Ver Resultados
                </button>
            </aside>

            <div class="w-full lg:w-3/4">
                
                @if($search || !empty($selectedCategories) || !empty($selectedBrands))
                    <div class="flex flex-wrap gap-2 mb-6 p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                        <span class="text-sm text-gray-500 flex items-center mr-2"><i class="ph-bold ph-funnel mr-1"></i> Filtros ativos:</span>
                        @if($search)
                            <button wire:click="$set('search', '')" class="bg-red-50 hover:bg-red-100 text-zandu-red px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition">
                                "{{ $search }}" <i class="ph-bold ph-x"></i>
                            </button>
                        @endif
                        @if(!empty($selectedCategories))
                            <button wire:click="$set('selectedCategories', [])" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition">
                                Categorias <i class="ph-bold ph-x"></i>
                            </button>
                        @endif
                        @if(!empty($selectedBrands))
                            <button wire:click="$set('selectedBrands', [])" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition">
                                Marcas <i class="ph-bold ph-x"></i>
                            </button>
                        @endif
                        <button wire:click="$set('search', ''); $set('selectedCategories', []); $set('selectedBrands', [])" class="ml-auto text-xs font-bold text-gray-400 hover:text-zandu-red underline decoration-gray-300 hover:decoration-zandu-red transition">
                            Limpar tudo
                        </button>
                    </div>
                @endif

                @if($products->isEmpty())
                    <div class="bg-white rounded-3xl p-16 text-center border border-gray-100 shadow-sm flex flex-col items-center">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                            <i class="ph-duotone ph-magnifying-glass text-4xl text-gray-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Nenhum produto encontrado</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Não encontramos nada com esses critérios. Tente buscar por termos mais genéricos ou limpe os filtros.</p>
                        <button wire:click="$set('search', ''); $set('selectedCategories', []);" class="bg-gray-900 text-white px-8 py-3 rounded-full font-bold hover:bg-gray-800 transition shadow-lg">
                            Ver todos os produtos
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($products as $product)
                            <div wire:key="shop-product-{{ $product->id }}" class="group bg-white rounded-3xl p-4 hover:shadow-2xl transition-all duration-300 relative overflow-hidden border border-transparent hover:border-gray-100">
                                
                                @if($product->discount_percent > 0)
                                    <span class="absolute top-4 left-4 bg-red-100 text-zandu-red text-xs font-bold px-3 py-1 rounded-full z-10">
                                        -{{ $product->discount_percent }}% OFF
                                    </span>
                                @endif

                                <div class="relative h-64 bg-gray-50 rounded-2xl mb-4 flex items-center justify-center overflow-hidden cursor-pointer">
                                    <a href="{{ url('produto/' . $product->slug) }}" class="absolute inset-0 z-20"></a>
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-6 transform group-hover:scale-105 transition duration-500 mix-blend-multiply">
                                    
                                    <div class="absolute bottom-4 right-4 translate-y-10 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition duration-300 z-30">
                                        <button 
                                            wire:click.prevent="addToCart('{{ $product->id }}')"
                                            wire:loading.attr="disabled"
                                            class="bg-gray-900 text-white w-10 h-10 rounded-full shadow-lg flex items-center justify-center hover:bg-zandu-red transition disabled:opacity-50"
                                        >
                                            <i wire:loading.remove target="addToCart('{{ $product->id }}')" class="ph-bold ph-shopping-cart"></i>
                                            <i wire:loading target="addToCart('{{ $product->id }}')" class="ph ph-spinner animate-spin"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="px-2">
                                    <div class="flex items-center gap-1 mb-2">
                                        <i class="ph-fill ph-star text-yellow-400 text-sm"></i>
                                        <span class="text-xs font-bold text-gray-700">{{ number_format($product->avg_rating, 1) }}</span>
                                        <span class="text-xs text-gray-400">({{ $product->reviews_count }})</span>
                                    </div>

                                    <h3 class="font-bold text-gray-900 text-lg leading-tight mb-2 line-clamp-2 h-12">
                                        <a href="{{ url('produto/' . $product->slug) }}" class="hover:text-zandu-red transition">
                                            {{ $product->name }}
                                        </a>
                                    </h3>
                                    
                                    <div class="flex items-end justify-between mt-4">
                                        <div>
                                            @if($product->promotional_price && $product->promotional_price < $product->price)
                                                <p class="text-xs text-gray-400 line-through mb-0.5">
                                                    {{ number_format($product->price, 0, ',', '.') }} Kz
                                                </p>
                                                <p class="text-xl font-extrabold text-gray-900">
                                                    {{ number_format($product->promotional_price, 0, ',', '.') }} <span class="text-sm font-bold align-top text-gray-500 ml-0.5">Kz</span>
                                                </p>
                                            @else
                                                <p class="text-xl font-extrabold text-gray-900">
                                                    {{ number_format($product->price, 0, ',', '.') }} <span class="text-sm font-bold align-top text-gray-500 ml-0.5">Kz</span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-12">
                        {{ $products->links() }} 
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
</div>