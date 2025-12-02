<div class="min-h-screen bg-gray-50 font-sans text-gray-900 py-8 md:py-12">
    
    <div class="container mx-auto px-4 max-w-5xl">
        
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-100 mb-4">
                <img src="https://e-zandu.com/default/logo-dark.png" alt="Logo" class="w-10 h-10 object-contain"> 
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Finalizar Pedido</h1>
            <p class="text-gray-500 text-sm mt-1">Complete os dados abaixo para receber a sua encomenda.</p>
        </div>

        <form wire:submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <div class="lg:col-span-7 space-y-6">

                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm font-bold">1</span>
                            Entrega
                        </h2>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Selecione a Província</label>
                                <div class="grid grid-cols-1 gap-3">
                                    @foreach($shippingRates as $rate)
                                        <label class="relative border-2 {{ $selectedShippingRateId == $rate->id ? 'border-zandu-red bg-red-50/50' : 'border-gray-100 hover:border-gray-200' }} rounded-xl p-4 cursor-pointer transition flex justify-between items-center group">
                                            <div class="flex items-center gap-4">
                                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 {{ $selectedShippingRateId == $rate->id ? 'border-zandu-red' : 'border-gray-300' }}">
                                                    @if($selectedShippingRateId == $rate->id) <div class="w-2.5 h-2.5 bg-zandu-red rounded-full"></div> @endif
                                                </div>
                                                <input type="radio" wire:model.live="selectedShippingRateId" value="{{ $rate->id }}" class="hidden">
                                                <div>
                                                    <span class="block font-bold text-gray-900 group-hover:text-zandu-red transition">{{ $rate->province_name }}</span>
                                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                                        <i class="ph-fill ph-clock"></i> {{ $rate->estimated_delivery_time }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span class="font-bold text-sm text-gray-900 bg-white px-3 py-1 rounded-lg border border-gray-100 shadow-sm">
                                                {{ number_format($rate->rate, 0, ',', '.') }} Kz
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedShippingRateId') <span class="text-red-500 text-xs font-bold mt-2 block flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Endereço / Ponto de Referência</label>
                                <div class="relative">
                                    <i class="ph-bold ph-map-pin absolute left-4 top-3.5 text-gray-400"></i>
                                    <textarea wire:model="delivery_address" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 focus:bg-white focus:border-zandu-red outline-none transition resize-none font-medium" placeholder="Ex: Mutamba, Rua dos Mercadores..."></textarea>
                                </div>
                                @error('delivery_address') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm font-bold">2</span>
                            Pagamento
                        </h2>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Notas (Opcional)</label>
                            <input type="text" wire:model="order_notes" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:bg-white focus:border-zandu-red outline-none transition" placeholder="Ex: Cor, Tamanho, Deixar na portaria...">
                        </div>

                        <div class="space-y-4 mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase">Método de Pagamento</label>
                            @foreach($paymentMethods as $method)
                                <label class="relative border-2 {{ $selectedPaymentMethodId == $method->id ? 'border-zandu-red bg-red-50/50' : 'border-gray-100 hover:border-gray-200' }} rounded-xl p-5 cursor-pointer transition flex flex-col gap-3 group">
                                    <div class="flex items-center gap-4">
                                        <div class="w-5 h-5 mt-0.5 rounded-full border-2 flex-shrink-0 flex items-center justify-center {{ $selectedPaymentMethodId == $method->id ? 'border-zandu-red' : 'border-gray-300' }}">
                                            <input type="radio" wire:model.live="selectedPaymentMethodId" value="{{ $method->id }}" class="hidden">
                                            @if($selectedPaymentMethodId == $method->id) <div class="w-2.5 h-2.5 bg-zandu-red rounded-full"></div> @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="block font-bold text-gray-900 text-base group-hover:text-zandu-red transition">{{ $method->name }}</span>
                                                @if($method->type == 'bank_transfer') <i class="ph-fill ph-bank text-xl text-gray-400"></i> @endif
                                                @if($method->type == 'express') <i class="ph-fill ph-qr-code text-xl text-gray-400"></i> @endif
                                                @if($method->type == 'cash_on_delivery') <i class="ph-fill ph-money text-xl text-gray-400"></i> @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($selectedPaymentMethodId == $method->id)
                                        <div class="ml-9 text-sm text-gray-600 bg-white/50 p-3 rounded-lg border border-gray-100/50 animate-fade-in-up">
                                            <p class="mb-2">{{ $method->instructions }}</p>
                                            
                                            @if($method->type != 'cash_on_delivery')
                                                <div class="bg-gray-50 p-3 rounded border border-gray-200 text-xs space-y-1">
                                                    @if($method->bank_iban) <p><span class="font-bold">IBAN:</span> {{ $method->bank_iban }}</p> @endif
                                                    @if($method->bank_account_number) <p><span class="font-bold">Conta:</span> {{ $method->bank_account_number }}</p> @endif
                                                    @if($method->phone_number) <p><span class="font-bold">Tel:</span> {{ $method->phone_number }}</p> @endif
                                                    
                                                    <div class="pt-2 mt-2 border-t border-gray-200 font-bold text-zandu-red">
                                                        Total a Pagar: Kz {{ number_format($total, 0, ',', '.') }}
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </label>
                            @endforeach
                            @error('selectedPaymentMethodId') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @php
                            $selectedMethod = $paymentMethods->find($selectedPaymentMethodId);
                            $showUpload = $selectedMethod && in_array($selectedMethod->type, ['bank_transfer', 'express']);
                        @endphp

                        @if($showUpload)
                            <div class="animate-fade-in-up pt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Anexar Comprovativo</label>
                                
                                <label class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-blue-50/30 hover:border-blue-300 transition-all duration-300 group overflow-hidden">
                                    
                                    @if($proof)
                                        <div class="text-center z-10">
                                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-2 shadow-sm">
                                                <i class="ph-fill ph-check text-xl"></i>
                                            </div>
                                            <p class="font-bold text-green-700 text-xs">Ficheiro Selecionado</p>
                                            <p class="text-[10px] text-gray-500 mt-1 truncate max-w-[200px]">{{ $proof->getClientOriginalName() }}</p>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10 group-hover:-translate-y-1 transition duration-300">
                                            <i class="ph-duotone ph-cloud-arrow-up text-3xl text-gray-400 group-hover:text-blue-500 transition mb-2"></i>
                                            <p class="mb-1 text-sm text-gray-500 font-medium">Clique ou arraste o ficheiro</p>
                                            <p class="text-[10px] text-gray-400 uppercase font-bold">PDF, JPG, PNG</p>
                                        </div>
                                    @endif
                                    
                                    <input type="file" wire:model="proof" class="hidden" accept="image/*,.pdf">
                                    
                                    <div wire:loading wire:target="proof" class="absolute inset-0 bg-white/80 flex items-center justify-center backdrop-blur-sm z-20">
                                        <div class="flex items-center gap-2 text-blue-600 font-bold text-sm">
                                            <i class="ph-bold ph-spinner animate-spin"></i> Carregando...
                                        </div>
                                    </div>
                                </label>
                                @error('proof') <span class="text-red-500 text-xs font-bold mt-2 block flex items-center gap-1"><i class="ph-bold ph-warning-circle"></i> {{ $message }}</span> @enderror
                            </div>
                        @endif

                    </div>
                </div>

                <div class="lg:col-span-5">
                    <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 p-6 md:p-8 sticky top-8">
                        
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
                            <h2 class="text-lg font-bold text-gray-900">Resumo do Pedido</h2>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded-md">#{{ $cart->cart_number ?? substr($cart->id, 0, 8) }}</span>
                        </div>

                        <div class="space-y-4 mb-6 max-h-[240px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($cart->items as $item)
                                <div class="flex gap-4 items-center group">
                                    <div class="w-14 h-14 bg-white rounded-xl border border-gray-100 flex-shrink-0 p-1 shadow-sm">
                                        <img src="{{ $item->product->image_url ?? '' }}" class="w-full h-full object-contain mix-blend-multiply">
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-gray-900 text-sm line-clamp-2 leading-tight mb-1">{{ $item->product->name ?? $item->name }}</p>
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-500 bg-gray-50 px-2 py-0.5 rounded">Qtd: {{ $item->quantity }}</span>
                                            <span class="font-bold text-gray-700 text-sm">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} Kz</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mb-6 bg-gray-50 rounded-xl p-1 border border-gray-100">
                            @if($couponApplied)
                                <div class="flex justify-between items-center p-3">
                                    <div class="text-green-700 text-sm">
                                        <span class="font-bold flex items-center gap-1"><i class="ph-fill ph-ticket"></i> Cupão Ativo</span>
                                        <span class="text-xs uppercase opacity-80">{{ $couponApplied->code }}</span>
                                    </div>
                                    <button type="button" wire:click="removeCoupon" class="w-7 h-7 bg-white rounded-full text-red-500 hover:text-red-700 flex items-center justify-center shadow-sm transition">
                                        <i class="ph-bold ph-x"></i>
                                    </button>
                                </div>
                            @else
                                <div class="flex gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" wire:model="couponCodeInput" placeholder="Código promocional" class="w-full bg-white border-0 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-zandu-red uppercase shadow-sm">
                                    </div>
                                    <button type="button" wire:click="applyCoupon" class="bg-gray-900 text-white px-4 rounded-lg font-bold text-sm hover:bg-gray-800 transition shadow-sm">
                                        Aplicar
                                    </button>
                                </div>
                                @error('couponCodeInput') <span class="text-red-500 text-xs mt-2 block px-2 font-bold">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="space-y-3 border-t border-gray-100 pt-4 text-sm mb-8">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span class="font-medium text-gray-900">{{ number_format($subtotal, 0, ',', '.') }} Kz</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Frete</span>
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

                        <div class="bg-gray-50 -mx-6 -mb-8 p-6 md:p-8 border-t border-gray-100 rounded-b-[2rem]">
                            <div class="flex justify-between items-end mb-6">
                                <div>
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total a Pagar</span>
                                    <span class="font-extrabold text-zandu-red text-3xl leading-none tracking-tight">
                                        {{ number_format($total, 0, ',', '.') }} <span class="text-lg">Kz</span>
                                    </span>
                                </div>
                            </div>

                            <button type="submit" 
                                class="w-full bg-zandu-red text-white font-bold py-4 rounded-xl hover:bg-red-700 transition shadow-xl hover:shadow-2xl transform active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed group" 
                                wire:loading.attr="disabled">
                                
                                <span wire:loading.remove class="flex items-center gap-2 text-lg">
                                    Confirmar Pedido <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                                </span>
                                
                                <span wire:loading class="flex items-center gap-2">
                                    <i class="ph ph-spinner animate-spin text-xl"></i> Processando...
                                </span>
                            </button>
                            
                            <p class="text-center text-xs text-gray-400 mt-4 flex items-center justify-center gap-1">
                                <i class="ph-fill ph-lock-key"></i> Ambiente 100% Seguro
                            </p>
                        </div>

                    </div>
                </div>

            </div>
        </form>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
    </style>
</div>