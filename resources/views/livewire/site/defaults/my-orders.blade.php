<div class="bg-gray-50 min-h-screen py-12 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="lg:hidden mb-8 flex items-center gap-3">
            <a href="{{ route('my-account') }}" class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center text-gray-600">
                <i class="ph-bold ph-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Meus Pedidos</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="hidden lg:block lg:col-span-4 xl:col-span-3">
                <x-account-sidebar />
            </div>

            <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                <div class="hidden lg:flex bg-white p-8 rounded-[2rem] border border-gray-100 shadow-sm justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Histórico de Pedidos</h1>
                        <p class="text-sm text-gray-500 mt-1">Acompanhe o status e detalhes das suas compras.</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400">
                        <i class="ph-duotone ph-receipt text-2xl"></i>
                    </div>
                </div>
                
                @if($orders->isEmpty())
                    <div class="bg-white rounded-[2rem] p-16 text-center shadow-sm border border-gray-100">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 animate-bounce-slow">
                            <i class="ph-duotone ph-shopping-bag text-5xl text-gray-300"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-3">Nenhum pedido encontrado</h2>
                        <p class="text-gray-500 text-sm mb-8 max-w-xs mx-auto">Parece que você ainda não fez nenhuma compra. Explore nossa loja!</p>
                        <a href="{{ route('shop') }}" class="bg-gray-900 text-white px-8 py-3.5 rounded-xl font-bold text-sm hover:bg-zandu-red transition shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center gap-2">
                            Ir para a Loja <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach($orders as $order)
                            <div wire:key="order-{{ $order->id }}" class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-md transition-all duration-300 group">
                                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                                    
                                    <div class="flex items-start gap-5">
                                        <div class="w-14 h-14 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center flex-shrink-0 group-hover:bg-gray-900 group-hover:text-white transition-colors duration-300">
                                            <i class="ph-bold ph-package text-2xl text-gray-400 group-hover:text-white"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-3 mb-1.5">
                                                <h3 class="font-bold text-gray-900 text-lg">#{{ $order->cart_number}}</h3>
                                                
                                                @php
                                                    $statusConfig = [
                                                        'active'    => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-700', 'icon' => 'ph-clock'],
                                                        'paid'      => ['bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'icon' => 'ph-check-circle'],
                                                        'completed' => ['bg' => 'bg-green-50', 'text' => 'text-green-700', 'icon' => 'ph-package'],
                                                        'cancelled' => ['bg' => 'bg-red-50', 'text' => 'text-red-700', 'icon' => 'ph-x-circle'],
                                                    ];
                                                    $statusLabels = [
                                                        'active' => 'Pendente', 'paid' => 'Pago', 'completed' => 'Entregue', 'cancelled' => 'Cancelado',
                                                    ];
                                                    $currentStatus = $order->status ?? 'active';
                                                    $style = $statusConfig[$currentStatus] ?? $statusConfig['active'];
                                                @endphp
                                                
                                                <span class="{{ $style['bg'] }} {{ $style['text'] }} text-[10px] px-2.5 py-1 rounded-full uppercase font-bold flex items-center gap-1">
                                                    <i class="ph-bold {{ $style['icon'] }}"></i>
                                                    {{ $statusLabels[$currentStatus] ?? $currentStatus }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 font-medium flex items-center gap-2">
                                                <i class="ph-fill ph-calendar-blank"></i> {{ $order->created_at->format('d/m/Y') }} 
                                                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                                {{ $order->items_count }} itens
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6 w-full md:w-auto pt-4 md:pt-0 border-t md:border-t-0 border-gray-50">
                                        <div class="md:text-right">
                                            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-0.5">Total do Pedido</p>
                                            <p class="text-lg font-extrabold text-zandu-red">
                                                @php
                                                    $subtotal = $order->total_price;
                                                    $shipping = $order->shippingRate->rate ?? 0; 
                                                    $discount = $order->discount_amount ?? 0;
                                                    $grandTotal = ($subtotal + $shipping) - $discount;
                                                @endphp
                                                Kz {{ number_format($grandTotal, 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <div class="flex gap-3 w-full md:w-auto">
                                            <button wire:click="downloadInvoice('{{ $order->id }}')" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 hover:text-gray-900 transition flex items-center justify-center gap-2 flex-1 md:flex-none" title="Baixar Fatura">
                                                <i class="ph-bold ph-file-pdf text-lg"></i> <span class="md:hidden">Fatura</span>
                                            </button>
                                            
                                            <a href="{{ route('order.details', $order->id) }}" wire:navigate class="px-6 py-2.5 rounded-xl bg-gray-900 text-white text-xs font-bold hover:bg-zandu-red transition flex items-center justify-center gap-2 flex-1 md:flex-none shadow-md hover:shadow-lg">
                                                Ver Detalhes <i class="ph-bold ph-caret-right"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>