<div>
    {{-- 
      ======================================================================
      FEEDBACK DE "ADICIONADO AO CARRINHO" (Sem alteração)
      ======================================================================
    --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" 
             x-init="setTimeout(() => show = false, 4000)" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-5"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 transform translate-x-0"
             x-transition:leave-end="opacity-0 transform translate-x-5"
             class="fixed top-24 right-5 bg-green-600 text-white py-3 px-5 rounded-lg shadow-xl z-[100]">
            {{ session('message') }}
        </div>
    @endif

    {{-- 
      ======================================================================
      1. SEÇÃO HERO (✅ NOVO DESIGN "SPLIT")
      ======================================================================
    --}}
    <section class="bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                {{-- Coluna de Texto --}}
                <div class="lg:col-span-6 text-center lg:text-left">
                    <span class="font-semibold text-orange-600 uppercase tracking-widest">
                        E-Zandu E-commerce
                    </span>
                    <h1 class="mt-4 text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900">
                        Inovação que <span class="text-orange-500">Inspira</span>. Qualidade que <span class="text-orange-500">Dura</span>.
                    </h1>
                    <p class="mt-6 text-lg text-gray-600">
                        Descubra a seleção E-Zandu. Soluções inteligentes e design premium para transformar seu dia a dia.
                    </p>
                    <a href="#produtos" class="mt-10 inline-block bg-orange-600 text-white font-bold text-lg px-8 py-3 rounded-md shadow-lg hover:bg-orange-700 transition duration-300 transform hover:-translate-y-1">
                        Explorar Coleção
                    </a>
                </div>

                {{-- Coluna de Imagem --}}
                <div class="lg:col-span-6">
                    <div class="bg-white p-4 rounded-xl shadow-2xl relative">
                        
                        <img src="https://images.unsplash.com/photo-1593344484962-796b36d08b3c?ixlib=rb-4.0.3&q=80&w=1920&auto=format&fit=crop" 
                             alt="Produtos E-Zandu" 
                             class="w-full h-auto object-cover rounded-lg">
                        <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-orange-500 rounded-full z-[-1] opacity-50"></div>
                        <div class="absolute -top-4 -left-4 w-24 h-24 bg-gray-200 rounded-lg z-[-1] opacity-50"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- 
      ======================================================================
      2. SEÇÃO DE DIFERENCIAIS (Sem alteração)
      ======================================================================
    --}}
    <section class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-2">Por que escolher a E-Zandu?</h2>
            <p class="text-center text-gray-600 mb-12">Garantimos a melhor experiência de compra.</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div class="p-8">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 mx-auto text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V14.25m-17.25 4.5h1.125c.621 0 1.125-.504 1.125-1.125V14.25m0 0V4.875c0-.621.504-1.125 1.125-1.125h13.5c.621 0 1.125.504 1.125 1.125V14.25" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">Entrega Rápida</h3>
                    <p class="mt-2 text-gray-600">Receba seus produtos no conforto da sua casa em tempo recorde.</p>
                </div>
                <div class="p-8 border-l border-r border-gray-200">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 mx-auto text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">Qualidade Garantida</h3>
                    <p class="mt-2 text-gray-600">Trabalhamos apenas com os melhores fornecedores e produtos.</p>
                </div>
                <div class="p-8">
                    <div class="flex-shrink-0">
                        <svg class="h-12 w-12 mx-auto text-orange-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.211-.998-.582-1.317l-3.938-3.15c-.445-.357-1.04-.41-1.545-.14l-.09.083c-.156.117-.335.214-.525.285C13.17 15.17 12.09 15 11 15c-1.09 0-2.17-.17-3.182-.48a4.524 4.524 0 01-.525-.285l-.09-.083c-.505-.27-1.1-.217-1.545.14l-3.938 3.15c-.37.299-.581.78-.581 1.317v1.372A2.25 2.25 0 002.25 6.75z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-gray-900">Suporte Humanizado</h3>
                    <p class="mt-2 text-gray-600">Nossa equipe está pronta para te ajudar no que você precisar.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 
      ======================================================================
      3. SEÇÃO DE CATEGORIAS (✅ CENTRALIZADO E COM ÍCONE DINÂMICO)
      ======================================================================
    --}}
    <section class="bg-gray-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Explore Nossas Categorias</h2>
            
            {{-- 
              Layout alterado de 'grid' para 'flex flex-wrap justify-center'
              Isto garante que os itens fiquem sempre centralizados.
            --}}
            <div class="flex flex-wrap justify-center gap-6">
                
                @forelse($categories as $category)
                    {{-- 
                      Adicionado 'w-44' (width) para consistência no layout flex
                    --}}
                    <a href="#" class="group block w-44">
                        <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200 
                                    text-center
                                    hover:shadow-xl hover:border-orange-500 hover:-translate-y-1 
                                    transition-all duration-300 ease-in-out">
                            
                            {{-- 
                              Ícone SVG estático substituído por um componente dinâmico.
                              Isto vai renderizar o ícone com base no nome guardado 
                              em $category->icon (ex: 'heroicon-o-user-circle')
                            --}}
                            <x-dynamic-component 
                                :component="$category->icon" 
                                class="h-10 w-10 text-orange-500 mx-auto mb-4 transition-colors duration-300 group-hover:text-orange-600" />
                            
                            <h3 class="text-lg font-semibold text-gray-800 group-hover:text-orange-600 transition duration-300">
                                {{ $category->name }}
                            </h3>
                        </div>
                    </a>
                @empty
                    <p class="text-center col-span-full text-gray-500">Nenhuma categoria encontrada.</p>
                @endforelse
                
            </div>
        </div>
    </section>

    {{-- 
      ======================================================================
      4. SEÇÃO DE PRODUTOS (Design anterior com galeria no hover - Mantido)
      ======================================================================
    --}}
    <section class="bg-white py-20" id="produtos">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Nossos Destaques</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

                @forelse($products as $product)
                    <div x-data="{ mainImage: '{{ $product->image_url }}' }" 
                         class="bg-white rounded-lg shadow-xl overflow-hidden flex flex-col group border border-transparent hover:border-orange-500 transition-all duration-300 transform hover:-translate-y-1">
                        
                        <div class="relative overflow-hidden">
                            <a href="/produto/{{ $product->slug }}" wire:navigate>
                                <img class="w-full h-64 object-cover transform group-hover:scale-110 transition-transform duration-500 ease-in-out" 
                                     :src="mainImage" 
                                     alt="{{ $product->name }}">
                            </a>
                            <span class="absolute top-4 left-4 bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full z-10">NOVO</span>

                            {{-- GALERIA DE THUMBNAILS (aparece no hover) --}}
                            <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-black/70 to-transparent p-2
                                        opacity-0 group-hover:opacity-100 transition-opacity duration-300
                                        flex items-center justify-center space-x-2 z-20">

                                {{-- Thumbnail 1: Imagem principal --}}
                                <button @mouseenter="mainImage = '{{ $product->image_url }}'" 
                                        class="rounded ring-2 ring-transparent hover:ring-white transition-all cursor-pointer">
                                    <img src="{{ $product->image_url }}" alt="Thumbnail principal" class="w-14 h-14 object-cover rounded">
                                </button>

                                {{-- Loop pelas imagens da galeria (limitado a 3) --}}
                                @foreach($product->images->take(3) as $image)
                                    <button @mouseenter="mainImage = '{{ $image->getRawOriginal('url') }}'" 
                                            class="rounded ring-2 ring-transparent hover:ring-white transition-all cursor-pointer">
                                        <img src="{{ $image->getRawOriginal('url') }}" alt="Thumbnail {{ $loop->iteration }}" class="w-14 h-14 object-cover rounded">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        
                        {{-- Info do Card --}}
                        <div class="p-6 flex-grow flex flex-col">
                            <div class="flex-grow mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    <a href="/produto/{{ $product->slug }}" wire:navigate class="hover:text-orange-600 transition-colors duration-300">
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="text-gray-500 text-sm mt-1">{{ $product->short_description }}</p>
                            </div>
                            
                            <div class="text-3xl font-bold text-gray-900 mb-4">
                                Kz {{ number_format($product->price, 2, ',', '.') }}
                            </div>

                            {{-- Botão "Adicionar ao Carrinho" --}}
                            <button 
                                wire:click.prevent="addToCart('{{ $product->id }}')" 
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-70 bg-orange-400 cursor-not-allowed"
                                class="w-full bg-orange-600 text-white font-bold py-3 px-4 rounded-md hover:bg-orange-700 transition duration-300 flex items-center justify-center space-x-2">
                                
                                <svg wire:loading wire:target="addToCart('{{ $product->id }}')" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>

                                <svg wire:loading.remove wire:target="addToCart('{{ $product->id }}')" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c.61 0 1.121.448 1.121 1.007L18.6 18.75m-16.5-1.5a1.125 1.125 0 011.125-1.125h1.5a1.125 1.125 0 011.125 1.125m-3.75 0a1.125 1.125 0 00-1.125 1.125v.75c0 .621.504 1.125 1.125 1.125h1.5c.621 0 1.125-.504 1.125-1.125v-.75c0-.621-.504-1.125-1.125-1.125h-1.5z" />
                                </svg>

                                <span wire:loading.remove wire:target="addToCart('{{ $product->id }}')">
                                    Adicionar ao Carrinho
                                </span>
                                <span wire:loading wire:target="addToCart('{{ $product->id }}')">
                                    Adicionando...
                                </span>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-center col-span-full text-gray-500">Nenhum produto em destaque no momento.</p>
                @endforelse

            </div>
        </div>
    </section>

    {{-- 
      ======================================================================
      5. SEÇÃO CTA (CALL TO ACTION) (Sem alteração)
      ======================================================================
    --}}
    <section class="bg-orange-600">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h2 class="text-3xl md:text-4xl font-extrabold text-white">
                Pronto para encontrar o que precisa?
            </h2>
            <p class="mt-4 text-lg text-orange-100">
                Junte-se a milhares de clientes satisfeitos e descubra um novo mundo de possibilidades com a E-Zandu.
            </p>
            <a href="#" class="mt-8 inline-block bg-white text-orange-600 font-bold text-lg px-8 py-3 rounded-md shadow-lg hover:bg-gray-100 transition duration-300 transform hover:-translate-y-1">
                Criar minha conta
            </a>
        </div>
    </section>

</div>