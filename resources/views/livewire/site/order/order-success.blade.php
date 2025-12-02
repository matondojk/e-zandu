<div class="bg-gray-50 min-h-screen py-12 flex items-center justify-center font-sans">
    <div class="container mx-auto px-4 max-w-3xl">
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden relative border border-gray-100">
            
            <div class="h-2 w-full bg-gradient-to-r from-gray-900 via-zandu-red to-gray-900"></div>

            <div class="p-8 md:p-12 text-center">
                
                <div class="mb-8 relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 bg-green-100 rounded-full animate-ping opacity-50"></div>
                    <div class="relative w-24 h-24 bg-green-500 rounded-full flex items-center justify-center shadow-lg shadow-green-500/30 ring-4 ring-white">
                        <i class="ph-bold ph-check text-5xl text-white"></i>
                    </div>
                    <i class="ph-fill ph-star text-yellow-400 absolute -top-2 -right-4 text-2xl animate-bounce"></i>
                    <i class="ph-fill ph-confetti text-zandu-red absolute bottom-0 -left-6 text-3xl transform -rotate-12"></i>
                </div>

                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Pedido Confirmado!</h1>
                <p class="text-gray-500 text-lg mb-10 max-w-lg mx-auto leading-relaxed">
                    Obrigado, <span class="font-bold text-gray-900">{{ explode(' ', $cart->user->name ?? 'Cliente')[0] }}</span>! 🎉<br>
                    O seu pedido <span class="font-bold text-gray-900 bg-gray-100 px-2 py-0.5 rounded">#{{ $cart->cart_number ?? substr($cart->id, 0, 8) }}</span> foi registado com sucesso.
                </p>

                <div class="max-w-lg mx-auto mb-12">
                    <div class="flex justify-between items-center relative px-4">
                        <div class="absolute left-4 right-4 top-1/2 h-1 bg-gray-100 -z-10 rounded-full"></div>
                        <div class="absolute left-4 top-1/2 h-1 bg-green-500 -z-10 rounded-full w-1/3"></div> <div class="flex flex-col items-center gap-2 relative">
                            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-white shadow-md border-4 border-white z-10">
                                <i class="ph-bold ph-check"></i>
                            </div>
                            <span class="text-[10px] font-bold text-green-600 uppercase tracking-wider bg-white px-1">Recebido</span>
                        </div>

                        <div class="flex flex-col items-center gap-2 relative">
                            <div class="w-8 h-8 bg-white border-4 border-gray-200 rounded-full flex items-center justify-center text-gray-300 z-10">
                                <span class="w-2 h-2 bg-gray-200 rounded-full"></span>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white px-1">Processando</span>
                        </div>

                        <div class="flex flex-col items-center gap-2 relative">
                            <div class="w-8 h-8 bg-white border-4 border-gray-200 rounded-full flex items-center justify-center text-gray-300 z-10">
                                <span class="w-2 h-2 bg-gray-200 rounded-full"></span>
                            </div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider bg-white px-1">A caminho</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100 text-left relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-4 bg-[radial-gradient(circle,transparent_50%,#f9fafb_50%)] bg-[length:16px_16px] rotate-180 opacity-50"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-5">
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Data do Pedido</p>
                                <p class="font-bold text-gray-900 flex items-center gap-2">
                                    <i class="ph-duotone ph-calendar-blank text-gray-400"></i>
                                    {{ $cart->created_at->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Método de Pagamento</p>
                                <p class="font-bold text-gray-900 flex items-center gap-2">
                                    @if($cart->paymentMethod->type == 'express') <i class="ph-duotone ph-qr-code text-gray-400"></i>
                                    @elseif($cart->paymentMethod->type == 'bank_transfer') <i class="ph-duotone ph-bank text-gray-400"></i>
                                    @else <i class="ph-duotone ph-money text-gray-400"></i> @endif
                                    {{ $cart->paymentMethod->name }}
                                </p>
                            </div>
                             <div>
                                <p class="text-xs text-gray-400 uppercase font-bold tracking-wider mb-1">Endereço de Entrega</p>
                                <p class="font-medium text-gray-700 text-sm leading-relaxed">
                                    {{ $cart->user->delivery_address ?? 'Endereço não informado' }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4 bg-white rounded-2xl p-5 border border-gray-100">
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Subtotal</span>
                                <span>Kz {{ number_format($cart->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500">
                                <span>Entrega</span>
                                <span>Kz {{ number_format($cart->shippingRate->rate ?? 0, 0, ',', '.') }}</span>
                            </div>
                            @if($cart->discount_amount > 0)
                                <div class="flex justify-between text-sm text-green-600 font-bold">
                                    <span>Desconto</span>
                                    <span>- Kz {{ number_format($cart->discount_amount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            
                            <div class="border-t border-gray-100 pt-3 mt-2">
                                <div class="flex justify-between items-end">
                                    <span class="text-xs font-bold text-gray-400 uppercase">Total Pago</span>
                                    <span class="text-xl font-extrabold text-zandu-red">
                                        Kz {{ number_format($cart->total_price, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200/50">
                        <div class="flex gap-3 bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <i class="ph-fill ph-info text-blue-500 text-xl mt-0.5"></i>
                            <p class="text-sm text-blue-800 leading-relaxed">
                                <span class="font-bold">O que acontece agora?</span>
                                @if($cart->paymentMethod->type == 'cash_on_delivery')
                                    A nossa equipa irá ligar para confirmar a entrega. Mantenha o telemóvel ligado.
                                @else
                                    Estamos a verificar o seu pagamento. Assim que confirmado, receberá uma notificação no WhatsApp/Email.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('shop') }}" class="w-full sm:w-auto px-8 py-4 bg-gray-900 text-white rounded-xl font-bold hover:bg-zandu-red transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <i class="ph-bold ph-storefront"></i> Continuar a Comprar
                    </a>
                    
                    @auth
                        <a href="{{ route('my.orders') }}" class="w-full sm:w-auto px-8 py-4 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 hover:text-gray-900 transition flex items-center justify-center gap-2">
                            <i class="ph-bold ph-receipt"></i> Meus Pedidos
                        </a>
                    @else
                        <a href="https://wa.me/244923111222" target="_blank" class="w-full sm:w-auto px-8 py-4 bg-white border border-gray-200 text-green-600 rounded-xl font-bold hover:bg-green-50 transition flex items-center justify-center gap-2">
                            <i class="ph-bold ph-whatsapp-logo"></i> Falar no WhatsApp
                        </a>
                    @endauth
                </div>

            </div>
        </div>
        
        <div class="text-center mt-8">
            <p class="text-xs text-gray-400">ID do Pedido: {{ $cart->id }}</p>
        </div>

    </div>
</div>