<div class="bg-gray-50 min-h-screen py-12 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="hidden lg:block lg:col-span-4 xl:col-span-3">
                <x-account-sidebar />
            </div>

            <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                
                <div class="bg-white p-6 md:p-8 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('my.orders') }}" wire:navigate class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-600 hover:bg-gray-900 hover:text-white transition">
                            <i class="ph-bold ph-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2">
                                Pedido #{{ $order->cart_number }}
                            </h1>
                            <p class="text-sm text-gray-500 flex items-center gap-2">
                                <i class="ph-duotone ph-calendar"></i> {{ $order->created_at->format('d/m/Y \à\s H:i') }}
                            </p>
                        </div>
                    </div>
                    
                    <button wire:click="downloadInvoice" class="w-full md:w-auto bg-white border border-gray-200 text-gray-700 hover:border-gray-900 hover:text-gray-900 font-bold py-3 px-6 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-sm">
                        <i class="ph-bold ph-download-simple text-lg"></i> Baixar Fatura PDF
                    </button>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    
                    <div class="xl:col-span-2 space-y-6">
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
                            <div class="px-8 py-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-2">
                                <i class="ph-fill ph-basket text-zandu-red"></i>
                                <span class="font-bold text-gray-900 text-sm uppercase tracking-wider">Itens do Pedido</span>
                            </div>
                            <div class="divide-y divide-gray-50">
                                @foreach($order->items as $item)
                                    <div class="p-6 flex gap-5 hover:bg-gray-50/50 transition duration-200">
                                        <div class="w-16 h-16 bg-white rounded-xl border border-gray-100 p-2 flex-shrink-0 shadow-sm">
                                            <img src="{{ $item->product->image_url ?? '' }}" class="w-full h-full object-contain mix-blend-multiply">
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-gray-900 text-sm md:text-base mb-1 line-clamp-2">
                                                {{ $item->product->name ?? 'Produto Indisponível' }}
                                            </h3>
                                            <div class="flex justify-between items-end">
                                                <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md font-bold">
                                                    Qtd: {{ $item->quantity }}
                                                </div>
                                                <span class="font-bold text-gray-900 text-sm">
                                                    Kz {{ number_format($item->price, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="xl:hidden bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6">
                            <h3 class="font-bold text-gray-900 mb-4 text-xs uppercase tracking-wide">Status Atual</h3>
                            @php
                                $statusColors = [
                                    'active' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                    'paid' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'completed' => 'bg-green-100 text-green-700 border-green-200',
                                    'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                ];
                                $statusNames = ['active' => 'Pendente', 'paid' => 'Pago', 'completed' => 'Entregue', 'cancelled' => 'Cancelado'];
                                $st = $order->status ?? 'active';
                            @endphp
                            <div class="{{ $statusColors[$st] ?? 'bg-gray-100' }} rounded-xl p-4 text-center border font-bold shadow-sm">
                                {{ $statusNames[$st] ?? ucfirst($st) }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        
                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 md:p-8">
                            <h3 class="font-bold text-gray-900 mb-6 text-xs uppercase tracking-wider flex items-center gap-2">
                                <i class="ph-fill ph-currency-circle-dollar text-zandu-red"></i> Pagamento
                            </h3>
                            
                            <div class="space-y-3 text-sm mb-6 pb-6 border-b border-dashed border-gray-200">
                                <div class="flex justify-between text-gray-600">
                                    <span>Subtotal</span>
                                    <span class="font-medium">Kz {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 0, ',', '.') }}</span>
                                </div>
                                @if($order->discount_amount > 0)
                                    <div class="flex justify-between text-green-600 font-medium">
                                        <span>Desconto</span>
                                        <span>- Kz {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-gray-600">
                                    <span>Envio</span>
                                    <span class="font-medium">Kz {{ number_format($order->shippingRate->rate ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Total Pago</p>
                                    <p class="font-extrabold text-zandu-red text-xl">
                                        @php
                                            $total = ($order->items->sum(fn($i) => $i->price * $i->quantity) + ($order->shippingRate->rate ?? 0)) - ($order->discount_amount ?? 0);
                                        @endphp
                                        Kz {{ number_format($total, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 md:p-8">
                            <h3 class="font-bold text-gray-900 mb-4 text-xs uppercase tracking-wider flex items-center gap-2">
                                <i class="ph-fill ph-map-pin text-zandu-red"></i> Entrega
                            </h3>
                            
                            <div class="mb-4">
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Zona / Província</p>
                                <p class="text-sm font-bold text-gray-900">{{ $order->shippingRate->province_name ?? 'N/A' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase mb-1">Endereço</p>
                                <div class="bg-gray-50 p-4 rounded-xl border border-gray-100 text-sm text-gray-700 leading-relaxed font-medium">
                                    {{ $order->user->delivery_address ?? 'Endereço não informado' }}
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm p-6 md:p-8">
                            <h3 class="font-bold text-gray-900 mb-4 text-xs uppercase tracking-wider flex items-center gap-2">
                                <i class="ph-fill ph-credit-card text-zandu-red"></i> Método
                            </h3>
                            
                            <p class="text-sm font-bold text-gray-900 mb-4">{{ $order->paymentMethod->name ?? 'N/A' }}</p>
                            
                            <div class="mb-4">
                                @php
                                    // Reutilizando a lógica de cores
                                    $st = $order->status ?? 'active';
                                @endphp
                                <div class="{{ $statusColors[$st] ?? 'bg-gray-100' }} rounded-xl px-4 py-3 text-center border font-bold text-sm shadow-sm">
                                    {{ $statusNames[$st] ?? ucfirst($st) }}
                                </div>
                            </div>

                            @if($order->payment_proof_path)
                                <a href="{{ Storage::disk('public')->url($order->payment_proof_path) }}" target="_blank" class="w-full bg-gray-900 text-white hover:bg-zandu-red font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition shadow-md">
                                    <i class="ph-bold ph-file-image text-lg"></i> Ver Comprovativo
                                </a>
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>