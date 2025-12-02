<div>
    <header class="relative bg-gray-900 pt-16 pb-24 lg:pt-28 lg:pb-36 overflow-hidden mb-12">
        
        <div class="absolute top-[-20%] right-[-10%] w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[-20%] left-[-10%] w-[500px] h-[500px] bg-zandu-red/10 rounded-full blur-[100px] pointer-events-none"></div>
        
        <div class="container mx-auto px-4 relative z-10">
            <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-16">
                
                <div class="w-full lg:w-5/12 text-center lg:text-left">
                    <div class="inline-block bg-gray-800 border border-gray-700 text-white font-bold text-xs px-4 py-2 rounded-full uppercase tracking-wider mb-6 shadow-lg">
                        <span class="text-zandu-red mr-1">●</span> Nova Coleção
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-[1.1] mb-6 tracking-tight">
                        O Futuro da <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400">Tecnologia.</span>
                    </h1>
                    
                    <p class="text-gray-400 text-lg mb-8 leading-relaxed font-medium">
                        Encontre os melhores dispositivos com garantia oficial Zandu e entrega rápida em toda Angola.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <button onclick="document.getElementById('shop').scrollIntoView({behavior: 'smooth'})" class="bg-white text-gray-900 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-gray-100 transition shadow-[0_0_20px_rgba(255,255,255,0.15)] hover:-translate-y-1 duration-300">
                            Comprar Agora
                        </button>
                    </div>
                </div>

                <div class="w-full lg:w-7/12 relative">
                    <div class="relative rounded-[3rem] overflow-hidden border-4 border-gray-700 shadow-2xl bg-gray-800 group">
                        
                        <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1200&auto=format&fit=crop" 
                             alt="Laptop Setup Zandu" 
                             class="w-full h-[400px] md:h-[500px] object-cover transform group-hover:scale-105 transition duration-700 ease-out opacity-90 group-hover:opacity-100">
                             
                        <div class="absolute bottom-8 left-8 bg-gray-900/80 backdrop-blur-md p-4 rounded-2xl shadow-lg z-20 flex items-center gap-4 max-w-xs border border-gray-600">
                            <div class="w-12 h-12 bg-zandu-red rounded-xl flex items-center justify-center text-white text-2xl shadow-lg shadow-red-900/50">
                                <i class="ph-fill ph-rocket-launch"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase">Entrega Expresso</p>
                                <p class="text-sm font-bold text-white">Receba em 24h</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <section class="py-8">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Categorias</h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($categories as $category)
                    <a href="#" class="group bg-white rounded-2xl p-4 flex flex-col items-center justify-center border border-gray-100 hover:border-zandu-red hover:shadow-lg transition-all duration-300 h-32">
                        <i class="{{ $category->icon }} text-3xl text-gray-400 group-hover:text-zandu-red group-hover:scale-110 transition duration-300 mb-3"></i>
                        <span class="font-medium text-gray-600 group-hover:text-gray-900 text-sm text-center">
                            {{ $category->name }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="shop" class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight">Destaques da Semana</h2>
                    <p class="text-gray-500 mt-2">Os produtos mais desejados do momento.</p>
                </div>
                <a href="#" class="hidden md:flex items-center gap-2 text-gray-900 font-bold hover:text-zandu-red transition underline decoration-gray-300 hover:decoration-zandu-red">
                    Ver catálogo completo
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($featuredProducts as $product)
                    <div wire:key="product-{{ $product->id }}" class="group bg-white rounded-3xl p-4 hover:shadow-2xl transition-all duration-300 relative overflow-hidden border border-transparent hover:border-gray-100">
                        
                        @if($product->discount_percent > 0)
                            <span class="absolute top-4 left-4 bg-red-100 text-zandu-red text-xs font-bold px-3 py-1 rounded-full z-10">
                                -{{ $product->discount_percent }}% OFF
                            </span>
                        @endif

                        <div class="relative h-64 bg-gray-50 rounded-2xl mb-4 flex items-center justify-center overflow-hidden cursor-pointer">
                            <a href="{{ url('produto/' . $product->slug) }}" class="absolute inset-0 z-20"></a>
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-6 transform group-hover:scale-105 transition duration-500 mix-blend-multiply">
                            
                            <div class="absolute bottom-4 right-4 translate-y-10 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition duration-300 z-30">
                                <button wire:click.prevent="addToCart('{{ $product->id }}')" class="bg-gray-900 text-white w-10 h-10 rounded-full shadow-lg flex items-center justify-center hover:bg-zandu-red transition">
                                    <i class="ph-bold ph-shopping-cart"></i>
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
            
            <div class="mt-10 text-center md:hidden">
                <a href="#" class="inline-block bg-gray-900 text-white font-bold px-8 py-3 rounded-full hover:bg-gray-800 transition shadow-lg">
                    Ver todos os produtos
                </a>
            </div>
        </div>
    </section>

    <section class="py-10 px-4">
        <div class="container mx-auto">
            <div class="relative rounded-[2.5rem] overflow-hidden bg-black min-h-[450px] flex flex-col md:flex-row items-center justify-between p-8 md:p-16 shadow-2xl">
                <div class="absolute inset-0 z-0">
                     <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&q=80&w=1600" class="w-full h-full object-cover opacity-40" alt="Promo Banner">
                     <div class="absolute inset-0 bg-gradient-to-r from-black via-black/80 to-transparent"></div>
                </div>

                <div class="relative z-10 max-w-xl text-center md:text-left">
                    <span class="text-zandu-red font-bold tracking-wider uppercase text-sm mb-4 block">Oferta Limitada</span>
                    <h2 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                        Produtividade <br>
                        <span class="text-gray-400">Sem Limites.</span>
                    </h2>
                    <p class="text-gray-300 text-lg mb-8 leading-relaxed">
                        Atualize seu setup com a nova linha de MacBooks e acessórios ergonómicos. 
                        Desempenho máximo para profissionais exigentes.
                    </p>
                    <button class="bg-white text-black px-8 py-4 rounded-full font-bold hover:bg-gray-100 transition hover:scale-105 duration-300 flex items-center gap-2 mx-auto md:mx-0">
                        Explorar Ofertas <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section id="benefits" class="py-16 bg-white border-t border-gray-100">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 divide-y md:divide-y-0 md:divide-x divide-gray-100">
                
                <div class="flex items-center gap-6 px-4 py-4 md:py-0">
                    <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center text-2xl text-gray-900 flex-shrink-0">
                        <i class="ph-fill ph-truck"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Entrega Rápida</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Receba em 24h em Luanda e envio expresso para províncias.</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 px-4 py-4 md:py-0">
                    <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center text-2xl text-gray-900 flex-shrink-0">
                        <i class="ph-fill ph-shield-check"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Garantia Oficial</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Produtos 100% originais com garantia direta da marca.</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 px-4 py-4 md:py-0">
                    <div class="w-14 h-14 bg-gray-50 rounded-full flex items-center justify-center text-2xl text-gray-900 flex-shrink-0">
                        <i class="ph-fill ph-credit-card"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 text-lg">Pagamento Seguro</h3>
                        <p class="text-gray-500 text-sm leading-relaxed">Multicaixa Express, Transferência ou Pagamento na Entrega.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>