<div classA="bg-white">

    {{-- Feedback de Ações (Carrinho, Wishlist) --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-5"
             x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform translate-x-5"
             class="fixed top-24 right-5 bg-green-600 text-white py-3 px-5 rounded-lg shadow-xl z-[100]">
            {{ session('message') }}
        </div>
    @endif

    <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div>
                <div class="aspect-square bg-gray-100 rounded-lg shadow-lg overflow-hidden mb-4">
                    <img :src="$wire.activeImage" alt="Imagem principal do {{ $product->name }}"
                         class="w-full h-full object-cover transition-opacity duration-300">
                </div>
                
                @if (count($gallery) > 1)
                <div class="grid grid-cols-5 gap-2">
                    @foreach ($gallery as $image)
                        <div wire:click="changeActiveImage('{{ $image['url'] }}')"
                             class="aspect-square bg-gray-100 rounded-md overflow-hidden cursor-pointer border-2
                                    {{ $activeImage == $image['url'] ? 'border-orange-500' : 'border-transparent' }}
                                    hover:border-orange-300 transition">
                            <img src="{{ $image['url'] }}" alt="Miniatura" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="flex flex-col justify-center">
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 tracking-tight">
                    {{ $product->name }}
                </h1>
                
                <div class="flex items-center mt-3">
                    <div class="flex items-center">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="h-5 w-5 {{ $averageRating >= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 15l-5.878 3.09 1.123-6.545L.489 7.01l6.561-.955L10 0l2.95 6.055 6.561.955-4.756 4.445 1.123 6.545z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="ml-2 text-sm text-gray-500">({{ $reviews->count() }} avaliações)</span>
                </div>

                <div class="mt-5">
                    <span class="text-4xl font-bold text-orange-600">
                        Kz {{ number_format($product->price, 2, ',', '.') }}
                    </span>
                </div>

                <div class="mt-6 text-base text-gray-700 space-y-4">
                    <p>{{ $product->short_description }}</p>
                </div>

                <div class="mt-8">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantidade</label>
                    <div class="flex items-center border border-gray-300 rounded-md" style="width: 130px;">
                        <button wire:click="decrement"
                                class="w-10 h-10 text-xl font-bold text-gray-600 hover:bg-gray-100 rounded-l-md">-</button>
                        <input type="text" wire:model="quantity" readonly
                               class="w-14 h-10 text-center border-none p-0 focus:ring-0">
                        <button wire:click="increment"
                                class="w-10 h-10 text-xl font-bold text-gray-600 hover:bg-gray-100 rounded-r-md">+</button>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-4">
                    <button wire:click="addToCart" wire:loading.attr="disabled"
                            class="flex-1 bg-orange-600 text-white font-bold py-3 px-8 rounded-md shadow-lg
                                   hover:bg-orange-700 transition duration-300 transform hover:-translate-y-1
                                   flex items-center justify-center space-x-2">
                        <svg wire:loading wire:target="addToCart" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                           <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                           <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="addToCart">Adicionar ao Carrinho</span>
                        <span wire:loading wire:target="addToCart">Adicionando...</span>
                    </button>
                    
                    <button wire:click="toggleWishlist"
                            class="p-3 bg-gray-100 rounded-md text-gray-500 hover:bg-gray-200 hover:text-orange-500
                                   transition duration-300">
                        <span class="sr-only">Adicionar à lista de desejos</span>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" 
                             fill="{{ $inWishlist ? 'currentColor' : 'none' }}" 
                             viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 py-20">
        <div class="container mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div x-data="{ activeTab: 'description' }">
                <div class="border-b border-gray-200 mb-8">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'description'"
                                :class="activeTab === 'description' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                            Descrição
                        </button>
                        <button @click="activeTab = 'specs'"
                                :class="activeTab === 'specs' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                            Especificações
                        </button>
                        <button @click="activeTab = 'reviews'"
                                :class="activeTab === 'reviews' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-lg">
                            Avaliações ({{ $reviews->count() }})
                        </button>
                    </nav>
                </div>

                <div>
                    <div x-show="activeTab === 'description'" class="prose max-w-none text-gray-700">
                        {!! $product->description !!}
                    </div>

                    <div x-show="activeTab === 'specs'" style="display: none;">
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">SKU</dt> <dd class="text-gray-700">{{ $product->sku ?? 'N/A' }}</dd></div>
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Unidade</dt> <dd class="text-gray-700">{{ $product->unit ?? 'N/A' }}</dd></div>
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Peso</dt> <dd class="text-gray-700">{{ $product->weight ? $product->weight . ' kg' : 'N/A' }}</dd></div>
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Dimensão</dt> <dd class="text-gray-700">{{ $product->dimension ?? 'N/A' }}</dd></div>
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Volume</dt> <dd class="text-gray-700">{{ $product->volume ?? 'N/A' }}</dd></div>
                            @if($product->colors)
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Cores</dt> <dd class="text-gray-700">{{ implode(', ', $product->colors) }}</dd></div>
                            @endif
                            @if($product->sizes)
                            <div class="bg-white p-4 rounded-md shadow-sm"><dt class="font-medium text-gray-900">Tamanhos</dt> <dd class="text-gray-700">{{ implode(', ', $product->sizes) }}</dd></div>
                            @endif
                        </dl>
                    </div>

                    <div x-show="activeTab === 'reviews'" style="display: none;">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="md:col-span-2 space-y-6">
                                <h3 class="text-xl font-semibold">O que os clientes dizem</h3>
                                @forelse ($reviews as $review)
                                    <article class="p-6 bg-white rounded-lg shadow-sm border border-gray-100">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <svg class="h-5 w-5 {{ $review->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 15l-5.878 3.09 1.123-6.545L.489 7.01l6.561-.955L10 0l2.95 6.055 6.561.955-4.756 4.445 1.123 6.545z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                                        </div>
                                        <p class="font-semibold text-gray-800">{{ $review->user->name ?? 'Cliente' }}</p>
                                        <p class="mt-2 text-gray-600">{{ $review->comment }}</p>
                                    </article>
                                @empty
                                    <p class="text-gray-500">Este produto ainda não tem avaliações. Seja o primeiro!</p>
                                @endforelse
                            </div>

                            <div class="md:col-span-1">
                                <form wire:submit.prevent="submitReview" class="p-6 bg-white rounded-lg shadow-md border border-gray-100 space-y-4">
                                    <h3 class="text-xl font-semibold">Deixe sua avaliação</h3>
                                    
                                    @if (session()->has('review_message'))
                                        <div class="p-3 rounded-md bg-green-100 text-green-700 text-sm">
                                            {{ session('review_message') }}
                                        </div>
                                    @endif

                                    @auth
                                        <div>
                                            <label class="block font-medium mb-2">Sua nota:</label>
                                            <div class="flex space-x-1">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <button type="button" wire:click="$set('newRating', {{ $i }})">
                                                        <svg class="h-6 w-6 {{ $newRating >= $i ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 7.01l6.561-.955L10 0l2.95 6.055 6.561.955-4.756 4.445 1.123 6.545z"/>
                                                        </svg>
                                                    </button>
                                                @endfor
                                            </div>
                                        </div>

                                        <div>
                                            <label for="newComment" class="block font-medium mb-2">Seu comentário (opcional):</label>
                                            <textarea wire:model.defer="newComment" id="newComment" rows="4"
                                                      class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500"></textarea>
                                            @error('newComment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <button type="submit" wire:loading.attr="disabled"
                                                class="w-full bg-orange-600 text-white font-bold py-2 px-4 rounded-md hover:bg-orange-700 transition">
                                            <span wire:loading.remove>Enviar Avaliação</span>
                                            <span wire:loading>Enviando...</span>
                                        </button>
                                    @else
                                        <p class="text-gray-600">
                                            Você precisa <a href="{{ route('login') }}" wire:navigate class="text-orange-600 underline">fazer login</a> para deixar uma avaliação.
                                        </p>
                                    @endauth
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($crossSellProducts->count() > 0)
        <section class="bg-white py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Você também pode gostar</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach ($crossSellProducts as $relatedProduct)
                        {{-- Usando um card de produto similar ao da Home --}}
                        <div class="bg-white rounded-lg shadow-xl overflow-hidden flex flex-col group border border-transparent hover:border-orange-500 transition-all duration-300 transform hover:-translate-y-1">
                            <div class="relative overflow-hidden">
                                <a href="{{ route('product.detail', $relatedProduct->slug) }}" wire:navigate>
                                    <img class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-500" 
                                         src="{{ $relatedProduct->image_url }}" alt="{{ $relatedProduct->name }}">
                                </a>
                            </div>
                            <div class="p-6 flex-grow flex flex-col">
                                <h3 class="text-lg font-semibold text-gray-800 flex-grow mb-2">
                                    <a href="{{ route('product.detail', $relatedProduct->slug) }}" wire:navigate class="hover:text-orange-600">
                                        {{ $relatedProduct->name }}
                                    </a>
                                </h3>
                                <div class="text-2xl font-bold text-gray-900 mb-4">
                                    Kz {{ number_format($relatedProduct->price, 2, ',', '.') }}
                                </div>
                                <a href="{{ route('product.detail', $relatedProduct->slug) }}" wire:navigate 
                                   class="block w-full text-center bg-orange-600 text-white font-bold py-3 px-4 rounded-md hover:bg-orange-700 transition">
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</div>