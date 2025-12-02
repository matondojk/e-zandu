<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4">
        
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
                <i class="ph-fill ph-shopping-cart text-zandu-red"></i> Meu Carrinho
            </h1>
            <span class="text-gray-500 text-sm font-medium hidden md:block">
                {{ count($cartItems) }} itens adicionados
            </span>
        </div>

        @if(count($cartItems) > 0)
            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="w-full lg:w-2/3">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        
                        <div class="hidden md:grid grid-cols-12 gap-4 p-5 bg-gray-50/50 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                            <div class="col-span-6">Produto</div>
                            <div class="col-span-2 text-center">Preço Unit.</div>
                            <div class="col-span-2 text-center">Qtd.</div>
                            <div class="col-span-2 text-right">Total</div>
                        </div>

                        <div class="divide-y divide-gray-100">
                            @foreach($cartItems as $item)
                                <div class="p-5 md:p-6 flex flex-col md:grid md:grid-cols-12 gap-6 md:gap-4 items-center group hover:bg-gray-50/30 transition duration-300">
                                    
                                    <div class="col-span-6 w-full flex items-center gap-5">
                                        <div class="w-24 h-24 flex-shrink-0 bg-white rounded-xl border border-gray-100 p-2 flex items-center justify-center shadow-sm">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-contain mix-blend-multiply">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-gray-900 text-base leading-tight mb-1">
                                                <a href="{{ route('product.details', \App\Models\Product::find($item['id'])->slug ?? '#') }}" class="hover:text-zandu-red transition line-clamp-2">
                                                    {{ $item['name'] }}
                                                </a>
                                            </h3>
                                            <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-2">
                                                @if(isset($item['brand'])) <span>{{ $item['brand'] }}</span> @endif
                                            </div>
                                            
                                            <button 
                                                wire:click="removeItem('{{ $item['id'] }}')" 
                                                wire:loading.attr="disabled"
                                                class="text-red-500 text-xs font-bold hover:text-red-700 flex items-center gap-1 transition disabled:opacity-50"
                                            >
                                                <span wire:loading.remove target="removeItem('{{ $item['id'] }}')"><i class="ph-bold ph-trash"></i> Remover</span>
                                                <span wire:loading target="removeItem('{{ $item['id'] }}')"><i class="ph ph-spinner animate-spin"></i> Removendo...</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-span-2 text-center text-sm text-gray-500 font-medium hidden md:block">
                                        Kz {{ number_format($item['price'], 0, ',', '.') }}
                                    </div>

                                    <div class="col-span-2 flex justify-center w-full md:w-auto">
                                        <div class="flex items-center bg-white border border-gray-200 rounded-xl shadow-sm">
                                            <button wire:click="updateQuantity('{{ $item['id'] }}', -1)" class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 text-gray-600 transition rounded-l-xl active:scale-95">
                                                <i class="ph-bold ph-minus text-xs"></i>
                                            </button>
                                            <span class="w-10 text-center text-sm font-bold text-gray-900 select-none">{{ $item['quantity'] }}</span>
                                            <button wire:click="updateQuantity('{{ $item['id'] }}', 1)" class="w-9 h-9 flex items-center justify-center hover:bg-gray-100 text-gray-600 transition rounded-r-xl active:scale-95">
                                                <i class="ph-bold ph-plus text-xs"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-span-2 w-full md:w-auto flex justify-between md:block md:text-right">
                                        <span class="md:hidden text-sm font-bold text-gray-500">Subtotal:</span>
                                        <span class="font-bold text-gray-900 text-lg">
                                            Kz {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-900 font-bold transition text-sm group">
                            <i class="ph-bold ph-arrow-left group-hover:-translate-x-1 transition-transform"></i> Continuar a comprar
                        </a>
                    </div>
                </div>

                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-3xl shadow-lg p-8 sticky top-28 border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center justify-between">
                            Resumo
                            <span class="text-xs font-normal bg-gray-100 px-2 py-1 rounded-md text-gray-500">{{ count($cartItems) }} itens</span>
                        </h2>

                        <div class="mb-8">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Cupão de Desconto</label>
                            @if(Session::has('coupon'))
                                <div class="flex justify-between items-center bg-green-50 border border-green-200 rounded-xl p-3 text-sm text-green-700 animate-pulse-slow">
                                    <div class="flex items-center gap-2">
                                        <i class="ph-fill ph-ticket text-lg"></i> 
                                        <div>
                                            <span class="font-bold block uppercase">{{ Session::get('coupon')['code'] }}</span>
                                            <span class="text-xs">Desconto aplicado</span>
                                        </div>
                                    </div>
                                    <button wire:click="removeCoupon" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white text-red-500 hover:text-red-700 transition shadow-sm">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" wire:model="couponCode" placeholder="Código promocional" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-gray-900 focus:ring-1 focus:ring-gray-900 uppercase transition placeholder-gray-400">
                                        <i class="ph-bold ph-tag absolute right-3 top-3.5 text-gray-400"></i>
                                    </div>
                                    <button wire:click="applyCoupon" class="bg-gray-900 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-gray-800 transition shadow-md">
                                        Aplicar
                                    </button>
                                </div>
                                @error('couponCode') <span class="text-red-500 text-xs mt-1 font-bold block flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="space-y-3 mb-8 pb-8 border-b border-dashed border-gray-200 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-semibold text-gray-900">Kz {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($discountAmount > 0)
                                <div class="flex justify-between text-green-600 font-bold bg-green-50 p-2 rounded-lg">
                                    <span>Desconto</span>
                                    <span>- Kz {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            
                            <div class="flex justify-between text-gray-500">
                                <span>Envio</span>
                                <span class="text-xs bg-gray-100 px-2 py-1 rounded font-bold text-gray-600">Calculado no checkout</span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end mb-8">
                            <div>
                                <span class="block text-xs text-gray-500 mb-1">Total Estimado</span>
                                <span class="font-extrabold text-gray-900 text-3xl leading-none">
                                    Kz {{ number_format($total, 0, ',', '.') }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-gray-400 block">IVA incluído</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout') }}" class="w-full bg-gray-900 text-white font-bold text-lg py-4 rounded-xl hover:bg-zandu-red transition shadow-xl hover:shadow-2xl transform hover:-translate-y-1 flex justify-center items-center gap-3 group">
                            Finalizar Compra <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        
                        <div class="mt-6 flex justify-center gap-4 text-gray-300 text-2xl grayscale opacity-70">
                            <i class="ph-fill ph-credit-card" title="Multicaixa"></i>
                            <i class="ph-fill ph-bank" title="Transferência"></i>
                            <i class="ph-fill ph-money" title="Dinheiro"></i>
                        </div>
                        <p class="text-center text-xs text-gray-400 mt-2">Compra 100% Segura e Garantida</p>
                    </div>
                </div>

            </div>
        @else
            <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-gray-100 max-w-xl mx-auto mt-10">
                <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 animate-float">
                    <i class="ph-duotone ph-shopping-cart text-6xl text-gray-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-3">Seu carrinho está vazio</h2>
                <p class="text-gray-500 mb-8 leading-relaxed">Parece que você ainda não adicionou nada. Explore nossa loja e encontre as melhores ofertas em tecnologia.</p>
                <a href="{{ route('shop') }}" class="bg-gray-900 text-white px-10 py-4 rounded-xl font-bold hover:bg-zandu-red transition shadow-lg hover:shadow-xl inline-flex items-center gap-2 transform hover:scale-105 duration-200">
                    Começar a comprar <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>
    
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</div>