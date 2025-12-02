<div x-data="{ mobileMenuOpen: false, cartOpen: false }">

    <div class="fixed top-0 left-0 w-full h-auto min-h-[40px] bg-yellow-400 text-yellow-900 z-[60] flex items-center justify-center px-4 text-center text-xs md:text-sm font-bold shadow-md">
        <p>
            ⚠️ Loja de demonstração utilizando IA e-Zandu.
            <a href="https://e-zandu.com" target="_blank" class="underline hover:text-black ml-1">e-zandu.com</a>.
            Compras não serão processadas.
        </p>
    </div>

    <nav class="fixed w-full top-[40px] z-50 transition-all duration-300 bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100">
        <div class="container mx-auto px-4 md:px-6 h-20 flex items-center justify-between">

            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="https://e-zandu.com/default/logo-dark.png" alt="Zandu Logo"
                    class="h-10 w-auto object-contain hover:opacity-90 transition">
            </a>

            <div class="hidden md:flex items-center space-x-8 font-semibold text-sm tracking-wide">
                <a href="{{ route('home') }}"
                    class="{{ request()->routeIs('home') ? 'text-zandu-red' : 'text-gray-600 hover:text-zandu-red' }} transition">
                    Início
                </a>

                <a href="{{ route('shop') }}"
                    class="{{ request()->routeIs('shop') ? 'text-zandu-red' : 'text-gray-600 hover:text-zandu-red' }} transition">
                    Loja
                </a>

                <a href="{{ route('about') }}"
                   class="{{ request()->routeIs('about') ? 'text-zandu-red' : 'text-gray-600 hover:text-zandu-red' }} transition">
                    Quem Somos
                </a>
                
                <a href="{{ route('contact') }}"
                   class="{{ request()->routeIs('contact') ? 'text-zandu-red' : 'text-gray-600 hover:text-zandu-red' }} transition">
                    Contato
                </a>
            </div>

            <div class="flex items-center space-x-2 md:space-x-4">

                <button class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition group">
                    <i class="ph ph-magnifying-glass text-2xl group-hover:text-zandu-red transition"></i>
                </button>

                <div class="relative group">
                    @auth
                        <button class="flex items-center gap-2 p-1 pr-3 rounded-full hover:bg-gray-50 border border-transparent hover:border-gray-200 transition">
                            <div class="w-8 h-8 bg-zandu-red text-white rounded-full flex items-center justify-center font-bold shadow-sm overflow-hidden">
                                @if(Auth::user()->avatar)
                                     <img src="{{ filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : Storage::disk('public')->url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                     {{ substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </div>
                            <span class="hidden lg:block text-sm font-semibold text-gray-700">{{ explode(' ', Auth::user()->name)[0] }}</span>
                            <i class="ph-bold ph-caret-down text-xs text-gray-400"></i>
                        </button>

                        <div class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top-right z-50">
                            <div class="py-2">
                                <div class="px-4 py-3 border-b border-gray-50 mb-1">
                                    <p class="text-xs text-gray-400 uppercase font-bold">Logado como</p>
                                    <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->email }}</p>
                                </div>
                                
                                <a href="{{ route('my.orders') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-zandu-red transition">
                                    <i class="ph-bold ph-package mr-3 text-lg"></i> Meus Pedidos
                                </a>
                                <a href="{{ route('my-account') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-zandu-red transition">
                                    <i class="ph-bold ph-user mr-3 text-lg"></i> Meus Dados
                                </a>
                                
                                <div class="border-t border-gray-50 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition flex items-center">
                                            <i class="ph-bold ph-sign-out mr-3 text-lg"></i> Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition group relative" title="Entrar / Criar Conta">
                            <i class="ph ph-user text-2xl group-hover:text-zandu-red transition"></i>
                        </a>
                    @endauth
                </div>

                <div class="relative group">
                    <a href="{{ route('cart') }}" class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition relative block group">
                        <i class="ph ph-shopping-cart text-2xl group-hover:text-zandu-red transition"></i>
                        @if ($cartCount > 0)
                            <span class="absolute top-0 right-0 bg-zandu-red text-white text-[10px] font-bold h-5 w-5 flex items-center justify-center rounded-full shadow-sm border-2 border-white transform scale-100 group-hover:scale-110 transition">
                                {{ $cartCount }}
                            </span>
                        @endif
                    </a>
                    
                    <div class="absolute right-0 top-full mt-4 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 transform translate-y-2 group-hover:translate-y-0 z-50">
                        <div class="absolute -top-2 right-3 w-4 h-4 bg-white transform rotate-45 border-l border-t border-gray-100"></div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 mb-3 flex justify-between items-center border-b border-gray-50 pb-2">
                                Seu Carrinho <span class="text-xs font-normal text-gray-500">{{ $cartCount }} itens</span>
                            </h3>
                            @php $cartSession = session('cart', []); $previewItems = array_slice($cartSession, 0, 3); @endphp
                            @if(count($cartSession) > 0)
                                <div class="space-y-3 mb-4">
                                    @foreach($previewItems as $item)
                                        <div class="flex gap-3">
                                            <div class="w-12 h-12 bg-gray-50 rounded-lg border border-gray-100 flex-shrink-0 overflow-hidden">
                                                <img src="{{ $item['image'] }}" class="w-full h-full object-contain mix-blend-multiply">
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $item['name'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $item['quantity'] }}x <span class="text-zandu-red font-bold">{{ number_format($item['price'], 0, ',', '.') }} Kz</span></p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if(count($cartSession) > 3) <p class="text-center text-xs text-gray-400 italic">+ {{ count($cartSession) - 3 }} outros itens</p> @endif
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('cart') }}" class="py-2.5 text-center text-sm font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Ver Carrinho</a>
                                    <a href="{{ route('checkout') }}" class="py-2.5 text-center text-sm font-bold text-white bg-zandu-red hover:bg-red-700 rounded-lg transition shadow-md">Checkout</a>
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <i class="ph-duotone ph-shopping-cart text-2xl text-gray-400"></i>
                                    </div>
                                    <p class="text-sm text-gray-500">Seu carrinho está vazio.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-gray-700 hover:bg-gray-100 rounded-full transition">
                    <i class="ph ph-list text-2xl" x-show="!mobileMenuOpen"></i>
                    <i class="ph ph-x text-2xl" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>
        </div>

        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-white border-t border-gray-100 absolute w-full shadow-xl h-[calc(100vh-80px)] overflow-y-auto pb-20" 
             style="display: none;">
            
            <div class="flex flex-col p-6 space-y-2">
                <a href="{{ route('home') }}" class="p-3 rounded-xl text-lg font-semibold text-gray-800 hover:bg-gray-50 hover:text-zandu-red flex justify-between items-center transition">
                    Início <i class="ph-bold ph-caret-right text-gray-300"></i>
                </a>
                <a href="{{ route('shop') }}" class="p-3 rounded-xl text-lg font-semibold text-gray-800 hover:bg-gray-50 hover:text-zandu-red flex justify-between items-center transition">
                    Loja <i class="ph-bold ph-caret-right text-gray-300"></i>
                </a>
                <a href="{{ route('cart') }}" class="p-3 rounded-xl text-lg font-semibold text-gray-800 hover:bg-gray-50 hover:text-zandu-red flex justify-between items-center transition">
                    Carrinho <span class="bg-zandu-red text-white text-xs font-bold px-2 py-1 rounded-full">{{ $cartCount }}</span>
                </a>
                <a href="{{ route('about') }}" class="p-3 rounded-xl text-lg font-semibold text-gray-800 hover:bg-gray-50 hover:text-zandu-red flex justify-between items-center transition">
                    Quem Somos <i class="ph-bold ph-caret-right text-gray-300"></i>
                </a>
                <a href="{{ route('contact') }}" class="p-3 rounded-xl text-lg font-semibold text-gray-800 hover:bg-gray-50 hover:text-zandu-red flex justify-between items-center transition">
                    Contato <i class="ph-bold ph-caret-right text-gray-300"></i>
                </a>
                
                <hr class="border-gray-100 my-4">
                
                @auth
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-zandu-red text-white rounded-full flex items-center justify-center font-bold text-lg overflow-hidden">
                                @if(Auth::user()->avatar)
                                     <img src="{{ filter_var(Auth::user()->avatar, FILTER_VALIDATE_URL) ? Auth::user()->avatar : Storage::disk('public')->url(Auth::user()->avatar) }}" class="w-full h-full object-cover">
                                @else
                                     {{ substr(Auth::user()->name, 0, 1) }}
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <a href="{{ route('my.orders') }}" class="block w-full py-2 px-4 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 text-center hover:bg-gray-50">Meus Pedidos</a>
                            <a href="{{ route('my-account') }}" class="block w-full py-2 px-4 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-700 text-center hover:bg-gray-50">Meus Dados</a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="block w-full py-2 px-4 bg-red-50 border border-red-100 rounded-lg text-sm font-bold text-red-600 text-center hover:bg-red-100">Sair da Conta</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="bg-gray-900 text-white text-center py-4 rounded-xl font-bold shadow-lg hover:bg-gray-800 transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-user"></i> Entrar / Criar Conta
                    </a>
                @endauth
            </div>
        </div>
    </nav>
</div>