<div class="bg-gray-50 min-h-screen py-12">
    <div class="container mx-auto px-4">
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8 flex items-center gap-3">
            <i class="ph-fill ph-lock-key text-zandu-red"></i> Checkout Seguro
        </h1>

        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-2/3 space-y-6">
                
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ Auth::check() ? 'bg-green-100 text-green-600' : 'bg-gray-900 text-white' }}">
                                @if(Auth::check()) <i class="ph-bold ph-check"></i> @else 1 @endif
                            </span>
                            Identificação
                        </h2>

                        @guest
                            <div class="flex flex-col sm:flex-row gap-4 mb-8">
                                <label class="flex-1 relative border-2 {{ $authMode === 'login' ? 'border-zandu-red bg-red-50/30' : 'border-gray-100 hover:border-gray-300' }} rounded-2xl p-4 cursor-pointer transition flex items-center gap-4 group">
                                    <div class="flex items-center justify-center">
                                        <input type="radio" wire:click="$set('authMode', 'login')" name="auth_mode" class="w-5 h-5 text-zandu-red focus:ring-zandu-red border-gray-300" {{ $authMode === 'login' ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <span class="block font-bold text-gray-900">Já tenho conta</span>
                                        <span class="text-xs text-gray-500">Aceder com email/telefone</span>
                                    </div>
                                </label>

                                <label class="flex-1 relative border-2 {{ $authMode === 'register' ? 'border-zandu-red bg-red-50/30' : 'border-gray-100 hover:border-gray-300' }} rounded-2xl p-4 cursor-pointer transition flex items-center gap-4 group">
                                    <div class="flex items-center justify-center">
                                        <input type="radio" wire:click="$set('authMode', 'register')" name="auth_mode" class="w-5 h-5 text-zandu-red focus:ring-zandu-red border-gray-300" {{ $authMode === 'register' ? 'checked' : '' }}>
                                    </div>
                                    <div>
                                        <span class="block font-bold text-gray-900">Criar conta</span>
                                        <span class="text-xs text-gray-500">É rápido e gratuito</span>
                                    </div>
                                </label>
                            </div>

                            <div class="transition-all duration-300 bg-gray-50 rounded-2xl p-6 border border-gray-100">
                                @if($authMode === 'login')
                                    <form wire:submit.prevent="loginUser" class="max-w-md space-y-5">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Email ou Telefone</label>
                                            <div class="relative">
                                                <i class="ph-bold ph-user absolute left-4 top-3.5 text-gray-400"></i>
                                                <input type="text" wire:model="loginInput" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition" placeholder="Seu acesso">
                                            </div>
                                            @error('loginInput') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Senha</label>
                                            <div class="relative">
                                                <i class="ph-bold ph-lock-key absolute left-4 top-3.5 text-gray-400"></i>
                                                <input type="password" wire:model="loginPassword" class="w-full bg-white border border-gray-200 rounded-xl pl-11 pr-4 py-3 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition" placeholder="Sua senha">
                                            </div>
                                            @error('loginPassword') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <button type="submit" class="w-full bg-gray-900 text-white font-bold py-3.5 rounded-xl hover:bg-gray-800 transition flex justify-center items-center gap-2 shadow-md">
                                            <span wire:loading.remove target="loginUser">Entrar e Continuar</span>
                                            <span wire:loading target="loginUser"><i class="ph ph-spinner animate-spin"></i> Validando...</span>
                                        </button>
                                    </form>
                                @else
                                    <form wire:submit.prevent="registerUser" class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Nome Completo</label>
                                            <input type="text" wire:model="registerName" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red outline-none transition" placeholder="Seu nome">
                                            @error('registerName') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Email</label>
                                            <input type="email" wire:model="registerEmail" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red outline-none transition" placeholder="email@exemplo.com">
                                            @error('registerEmail') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">WhatsApp</label>
                                            <input type="text" wire:model="registerPhone" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red outline-none transition" placeholder="9XX XXX XXX">
                                            @error('registerPhone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1.5">Senha</label>
                                            <input type="password" wire:model="registerPassword" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red outline-none transition" placeholder="Mínimo 6 caracteres">
                                            @error('registerPassword') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="md:col-span-2 pt-2">
                                            <button type="submit" class="w-full bg-zandu-red text-white font-bold py-3.5 rounded-xl hover:bg-red-700 transition flex justify-center items-center gap-2 shadow-md">
                                                <span wire:loading.remove target="registerUser">Criar Conta e Continuar</span>
                                                <span wire:loading target="registerUser"><i class="ph ph-spinner animate-spin"></i> Criando...</span>
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center justify-between bg-green-50 border border-green-100 rounded-2xl p-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-green-200 text-green-700 flex items-center justify-center font-bold text-xl">
                                        {{ substr(Auth::user()->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-lg">Olá, {{ Auth::user()->name }}</p>
                                        <p class="text-sm text-green-700 flex items-center gap-1 font-medium">
                                            <i class="ph-fill ph-check-circle"></i> Conta Autenticada
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-gray-600 hover:text-red-600 hover:bg-red-50 transition">
                                        Trocar Conta
                                    </button>
                                </form>
                            </div>
                        @endguest
                    </div>
                </div>

                <div class="{{ Auth::check() ? '' : 'opacity-50 pointer-events-none grayscale filter' }} transition-all duration-500 space-y-6 relative">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-bold">2</span>
                            Dados de Entrega
                        </h2>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Selecione a Província</label>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($shippingRates as $rate)
                                        <label class="relative border-2 {{ $selectedShippingRateId == $rate->id ? 'border-zandu-red bg-red-50' : 'border-gray-100 hover:border-gray-200' }} rounded-2xl p-4 cursor-pointer transition flex justify-between items-center group h-full">
                                            <div class="flex items-center gap-3">
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selectedShippingRateId == $rate->id ? 'border-zandu-red' : 'border-gray-300' }}">
                                                    @if($selectedShippingRateId == $rate->id) <div class="w-2.5 h-2.5 bg-zandu-red rounded-full"></div> @endif
                                                </div>
                                                <input type="radio" wire:model.live="selectedShippingRateId" value="{{ $rate->id }}" class="hidden">
                                                <div>
                                                    <span class="block font-bold text-gray-900 group-hover:text-zandu-red transition">{{ $rate->province_name }}</span>
                                                    <span class="text-xs text-gray-500 flex items-center gap-1"><i class="ph-fill ph-clock"></i> {{ $rate->estimated_delivery_time }}</span>
                                                </div>
                                            </div>
                                            <span class="font-bold text-sm text-gray-900 bg-white px-2 py-1 rounded-md border border-gray-100 shadow-sm">
                                                {{ number_format($rate->rate, 0, ',', '.') }} Kz
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedShippingRateId') <span class="text-red-500 text-xs font-bold mt-2 block flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Endereço Completo / Ponto de Referência</label>
                                <div class="relative">
                                    <i class="ph-bold ph-map-pin absolute left-4 top-3.5 text-gray-400"></i>
                                    <textarea wire:model="addressReference" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 focus:bg-white focus:border-zandu-red outline-none transition resize-none" placeholder="Ex: Mutamba, Rua dos Mercadores, Prédio Amarelo nº 10."></textarea>
                                </div>
                                @error('addressReference') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-bold">3</span>
                            Pagamento
                        </h2>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Notas do Pedido (Opcional)</label>
                            <input type="text" wire:model="orderNotes" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:border-zandu-red outline-none transition" placeholder="Ex: Cor Azul, deixar na recepção...">
                        </div>

                        <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Escolha como pagar</label>
                        <div class="space-y-3">
                            @foreach($paymentMethods as $method)
                                <label class="relative border-2 {{ $selectedPaymentMethodId == $method->id ? 'border-zandu-red bg-red-50' : 'border-gray-100 hover:border-gray-200' }} rounded-2xl p-5 cursor-pointer transition flex items-start gap-4 group">
                                    <div class="w-5 h-5 mt-1 rounded-full border-2 flex-shrink-0 flex items-center justify-center {{ $selectedPaymentMethodId == $method->id ? 'border-zandu-red' : 'border-gray-300' }}">
                                        <input type="radio" wire:model.live="selectedPaymentMethodId" value="{{ $method->id }}" class="hidden">
                                        @if($selectedPaymentMethodId == $method->id) <div class="w-2.5 h-2.5 bg-zandu-red rounded-full"></div> @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="block font-bold text-gray-900 text-lg group-hover:text-zandu-red transition">{{ $method->name }}</span>
                                            @if(str_contains(strtolower($method->name), 'referência')) <i class="ph-fill ph-qr-code text-xl text-gray-400"></i> @endif
                                            @if(str_contains(strtolower($method->name), 'transferência')) <i class="ph-fill ph-bank text-xl text-gray-400"></i> @endif
                                            @if(str_contains(strtolower($method->name), 'entrega')) <i class="ph-fill ph-hand-coins text-xl text-gray-400"></i> @endif
                                        </div>
                                        <p class="text-sm text-gray-600 leading-relaxed">{{ $method->instructions }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedPaymentMethodId') <span class="text-red-500 text-xs font-bold mt-2 block flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-3xl shadow-lg p-6 md:p-8 sticky top-24 border border-gray-100">
                    <h2 class="text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Resumo do Pedido</h2>

                    <div class="space-y-4 mb-6 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($cartItems as $item)
                            <div class="flex gap-3 text-sm group">
                                <div class="w-14 h-14 bg-white rounded-lg border border-gray-100 flex-shrink-0 p-1 shadow-sm">
                                    <img src="{{ $item['image'] }}" class="w-full h-full object-contain mix-blend-multiply">
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 line-clamp-2 leading-tight mb-1">{{ $item['name'] }}</p>
                                    <div class="flex justify-between items-center">
                                        <p class="text-gray-500 text-xs bg-gray-50 px-2 py-0.5 rounded">Qtd: {{ $item['quantity'] }}</p>
                                        <p class="font-bold text-gray-700">{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} Kz</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($couponApplied)
                        <div class="mb-6 bg-green-50 border border-green-200 rounded-xl p-3 flex justify-between items-center text-sm">
                            <div class="text-green-700">
                                <span class="font-bold flex items-center gap-1"><i class="ph-fill ph-tag"></i> Cupão Ativo</span>
                                <span class="text-xs uppercase">{{ $couponApplied['code'] }}</span>
                            </div>
                            <span class="text-green-700 font-bold">-{{ number_format($discountAmount, 0, ',', '.') }} Kz</span>
                        </div>
                    @endif

                    <div class="space-y-3 border-t border-gray-100 pt-4 text-sm mb-6">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium">{{ number_format($subtotal, 0, ',', '.') }} Kz</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Envio</span>
                            <span class="{{ $shippingCost > 0 ? 'font-bold text-gray-900' : 'text-gray-400' }}">
                                {{ $shippingCost > 0 ? number_format($shippingCost, 0, ',', '.') . ' Kz' : '--' }}
                            </span>
                        </div>
                        @if($discountAmount > 0)
                            <div class="flex justify-between text-green-600 font-bold">
                                <span>Desconto</span>
                                <span>- {{ number_format($discountAmount, 0, ',', '.') }} Kz</span>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-gray-200 border-dashed pt-4 mb-8">
                        <div class="flex justify-between items-end">
                            <span class="font-bold text-gray-900 text-lg">Total a Pagar</span>
                            <span class="font-extrabold text-zandu-red text-2xl">{{ number_format($total, 0, ',', '.') }} Kz</span>
                        </div>
                    </div>

                    <button 
                        wire:click="placeOrder" 
                        wire:loading.attr="disabled" 
                        class="w-full bg-zandu-red text-white font-bold text-lg py-4 rounded-xl hover:bg-red-700 transition shadow-xl hover:shadow-2xl transform active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group"
                        @if(!Auth::check()) disabled @endif
                    >
                        @if(Auth::check())
                            <span wire:loading.remove class="flex items-center gap-2">
                                Confirmar Pedido <i class="ph-bold ph-check-circle text-2xl"></i>
                            </span>
                            <span wire:loading><i class="ph ph-spinner animate-spin text-xl"></i> Processando...</span>
                        @else
                            <span>Faça login para finalizar</span>
                        @endif
                    </button>

                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-400 flex items-center justify-center gap-1">
                            <i class="ph-fill ph-shield-check"></i> Seus dados estão protegidos.
                        </p>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</div>