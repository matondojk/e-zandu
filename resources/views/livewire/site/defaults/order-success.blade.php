<div class="bg-gray-50 min-h-screen py-12 flex items-center justify-center font-sans">
    <div class="container mx-auto px-4 max-w-3xl">
        
        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden relative">
            
            <div class="h-3 w-full bg-gradient-to-r from-gray-900 via-zandu-red to-gray-900"></div>

            <div class="p-8 md:p-12 text-center">
                
                <div class="mb-8 relative inline-flex items-center justify-center">
                    <div class="absolute inset-0 bg-green-100 rounded-full animate-ping opacity-50"></div>
                    <div class="relative w-24 h-24 bg-green-500 rounded-full flex items-center justify-center shadow-lg shadow-green-500/30">
                        <i class="ph-fill ph-check text-5xl text-white"></i>
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">Obrigado pela sua compra!</h1>
                <p class="text-gray-500 text-lg mb-8 max-w-xl mx-auto leading-relaxed">
                    O seu pedido <span class="text-gray-900 font-bold bg-gray-100 px-2 py-0.5 rounded">#{{ substr($cart->id, 0, 8) }}</span> foi recebido com sucesso. Enviámos um email de confirmação com todos os detalhes.
                </p>

                <div class="max-w-lg mx-auto mb-10">
                    <div class="flex justify-between items-center relative">
                        <div class="absolute left-0 top-1/2 w-full h-1 bg-gray-100 -z-10 rounded-full"></div>
                        
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white shadow-sm border-4 border-white">
                                <i class="ph-bold ph-check"></i>
                            </div>
                            <span class="text-xs font-bold text-green-600 uppercase">Confirmado</span>
                        </div>

                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 bg-white border-4 border-gray-200 rounded-full flex items-center justify-center text-gray-400">
                                <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase">A Processar</span>
                        </div>

                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 bg-white border-4 border-gray-200 rounded-full flex items-center justify-center text-gray-400">
                                <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Envio</span>
                        </div>
                        
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 bg-white border-4 border-gray-200 rounded-full flex items-center justify-center text-gray-400">
                                <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase">Entregue</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-3xl p-8 border border-gray-100 text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Data do Pedido</p>
                                <p class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="ph-duotone ph-calendar-blank text-gray-400"></i>
                                    {{ $cart->created_at->format('d/m/Y \à\s H:i') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Método de Pagamento</p>
                                <p class="font-semibold text-gray-900 flex items-center gap-2">
                                    <i class="ph-duotone ph-credit-card text-gray-400"></i>
                                    {{ $cart->paymentMethod->name }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Total Pago</p>
                                <p class="text-2xl font-extrabold text-zandu-red">
                                    Kz {{ number_format($cart->total_price, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Previsão</p>
                                <p class="font-semibold text-gray-900 text-sm">
                                    @if($cart->paymentMethod->type == 'cash_on_delivery')
                                        A aguardar confirmação telefónica.
                                    @else
                                        Envio após validação do pagamento.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('shop') }}" class="px-8 py-4 bg-gray-900 text-white rounded-xl font-bold hover:bg-zandu-red transition shadow-lg transform hover:-translate-y-1">
                        Continuar a Comprar
                    </a>
                    
                    <a href="{{ Auth::check() ? route('my.orders') : '#' }}" class="px-8 py-4 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <i class="ph-bold ph-receipt"></i> Ver Detalhes do Pedido
                    </a>
                </div>

            </div>
        </div>
        
        <div class="text-center mt-8 text-gray-400 text-sm">
            <p>Precisa de ajuda? <a href="{{ route('contact') }}" class="underline hover:text-gray-600">Fale connosco</a></p>
        </div>

    </div>
</div>