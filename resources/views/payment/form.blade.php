<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pedido #{{ $cart->id }}</title>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'zandu-red': '#DC2626', // Ajuste para a cor exata da sua marca
                    }
                }
            }
        }
    </script>
    <style>
        /* Esconder scrollbar mas manter funcionalidade */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen font-sans text-gray-900">

    <div class="container mx-auto px-4 py-12">
        
        <h1 class="text-3xl font-bold text-gray-900 mb-8 flex items-center gap-3">
            <i class="ph-fill ph-lock-key text-zandu-red"></i> Checkout Seguro
        </h1>

        <form action="{{ route('payment.store', $cart->uuid) }}" method="POST" enctype="multipart/form-data" id="payment-form">
            @csrf
            
            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="w-full lg:w-2/3 space-y-6">

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm font-bold">1</span>
                            Entrega
                        </h2>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Província</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($shippingRates as $rate)
                                    <label class="shipping-option relative border-2 border-gray-100 hover:border-gray-300 rounded-xl p-4 cursor-pointer transition flex justify-between items-center group">
                                        <div class="flex items-center gap-3">
                                            <div class="radio-indicator w-5 h-5 rounded-full border-2 border-gray-300 flex items-center justify-center">
                                                <div class="w-2.5 h-2.5 bg-zandu-red rounded-full opacity-0 transition-opacity"></div>
                                            </div>
                                            
                                            <input type="radio" name="shipping_rate_id" value="{{ $rate->id }}" data-rate="{{ $rate->rate }}" class="hidden peer" required>
                                            
                                            <div>
                                                <span class="block font-bold text-gray-900 group-hover:text-zandu-red transition">{{ $rate->province_name }}</span>
                                                <span class="text-xs text-gray-500">Estimativa: {{ $rate->estimated_delivery_time ?? '2-5 dias' }}</span>
                                            </div>
                                        </div>
                                        <span class="font-bold text-sm text-gray-700">{{ $orderService->formatPrice($rate->rate) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('shipping_rate_id') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Morada Completa</label>
                            <textarea name="delivery_address" rows="2" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition" placeholder="Ex: Mutamba, Rua dos Mercadores, Prédio Amarelo nº 10." required>{{ old('delivery_address', $userAddress) }}</textarea>
                            @error('delivery_address') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                         <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Notas (Opcional)</label>
                            <input type="text" name="order_notes" value="{{ old('order_notes') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 focus:border-zandu-red outline-none transition" placeholder="Ex: Cor Azul, Tamanho M, etc...">
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <span class="w-8 h-8 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center text-sm font-bold">2</span>
                            Pagamento
                        </h2>

                        <div class="space-y-3 mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Método de Pagamento</label>
                            @foreach($paymentMethods as $method)
                                <label class="payment-option relative border-2 border-gray-100 hover:border-gray-300 rounded-xl p-4 cursor-pointer transition flex items-start gap-4 group">
                                    <input type="radio" name="payment_method_id" value="{{ $method->id }}" data-details="{{ $method->toJson() }}" class="hidden peer" required>
                                    
                                    <div class="radio-indicator w-5 h-5 mt-1 rounded-full border-2 border-gray-300 flex-shrink-0 flex items-center justify-center">
                                        <div class="w-2.5 h-2.5 bg-zandu-red rounded-full opacity-0 transition-opacity"></div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <span class="block font-bold text-gray-900 group-hover:text-zandu-red transition">{{ $method->name }}</span>
                                        @if($method->type == 'cash_on_delivery')
                                            <p class="text-xs text-gray-500 mt-1">Pagamento no ato da entrega.</p>
                                        @elseif($method->type == 'express')
                                            <p class="text-xs text-gray-500 mt-1">Pagamento via Multicaixa Express.</p>
                                        @else
                                            <p class="text-xs text-gray-500 mt-1">Transferência ou Depósito.</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                             @error('payment_method_id') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div id="payment-instructions" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-5 mb-6">
                            </div>

                        <div id="comprovativo-group" class="hidden">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Anexar Comprovativo</label>
                            <div class="flex items-center justify-center w-full">
                                <label for="comprovativo" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <i class="ph ph-upload-simple text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-sm text-gray-500"><span class="font-semibold">Clique para enviar</span> ou arraste</p>
                                        <p class="text-xs text-gray-500">PNG, JPG ou PDF</p>
                                    </div>
                                    <input id="comprovativo" name="comprovativo" type="file" class="hidden" accept="image/*,.pdf" />
                                </label>
                            </div>
                            @error('comprovativo') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                </div>

                <div class="w-full lg:w-1/3">
                    <div class="bg-white rounded-2xl shadow-lg p-6 sticky top-8 border border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900 mb-6 pb-4 border-b border-gray-100">Resumo do Pedido</h2>
                        <p class="text-sm text-gray-500 mb-4">Cliente: <span class="font-semibold text-gray-900">{{ $userName }}</span></p>

                        {{-- 
                        <div class="space-y-4 mb-6 max-h-60 overflow-y-auto hide-scrollbar pr-1">
                            @foreach($cart->items as $item) ... @endforeach
                        </div> 
                        --}}

                        <div class="mb-6 border-t border-gray-100 pt-4">
                            <div class="flex gap-2">
                                <input type="text" id="coupon_code_input" placeholder="Código de Cupão" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none uppercase transition">
                                <button type="button" id="apply-coupon-btn" class="bg-gray-900 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-gray-800 transition disabled:opacity-50">
                                    Aplicar
                                </button>
                            </div>
                            <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                            <span id="coupon-message" class="text-xs mt-2 block font-medium"></span>
                            @error('coupon_code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-2 border-t border-gray-100 pt-4 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span>{{ $subTotalFormatted }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Frete</span>
                                <span id="shipping-value" class="text-gray-900 font-medium">--</span>
                            </div>
                            <div id="discount-row" class="flex justify-between text-green-600 font-bold hidden">
                                <span>Desconto</span>
                                <span id="discount-value">- 0,00 Kz</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 mt-4 mb-6">
                            <div class="flex justify-between items-end">
                                <span class="font-bold text-gray-900 text-lg">Total</span>
                                <span id="total-value" class="font-extrabold text-zandu-red text-2xl">{{ $subTotalFormatted }}</span>
                            </div>
                        </div>

                        <button type="submit" id="submit-btn" class="w-full bg-zandu-red text-white font-bold py-4 rounded-xl hover:bg-red-700 transition shadow-lg flex justify-center items-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            Confirmar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- Variáveis Globais ---
            const subTotal = {{ $subTotalRaw }};
            const cartUuid = '{{ $cart->uuid }}';
            const csrfToken = document.querySelector('input[name="_token"]').value;
            
            let currentShippingRate = 0;
            let currentDiscount = 0;

            // --- Elementos ---
            const shippingRadios = document.querySelectorAll('input[name="shipping_rate_id"]');
            const paymentRadios = document.querySelectorAll('input[name="payment_method_id"]');
            
            const couponInput = document.getElementById('coupon_code_input');
            const couponHiddenInput = document.getElementById('coupon_code_hidden');
            const couponBtn = document.getElementById('apply-coupon-btn');
            const couponMsg = document.getElementById('coupon-message');
            
            const shippingValueEl = document.getElementById('shipping-value');
            const discountRow = document.getElementById('discount-row');
            const discountValueEl = document.getElementById('discount-value');
            const totalValueEl = document.getElementById('total-value');
            
            const instructionsEl = document.getElementById('payment-instructions');
            const comprovativoGroup = document.getElementById('comprovativo-group');
            const comprovativoInput = document.getElementById('comprovativo');
            const submitBtn = document.getElementById('submit-btn');

            // --- Funções Auxiliares de UI ---
            
            // Função para estilizar o Radio Button Selecionado
            function updateRadioStyles(radios, groupClass) {
                radios.forEach(radio => {
                    const label = radio.closest('label');
                    const indicator = label.querySelector('.radio-indicator div'); // A bolinha interna
                    const indicatorBorder = label.querySelector('.radio-indicator');

                    if (radio.checked) {
                        label.classList.add('border-zandu-red', 'bg-red-50');
                        label.classList.remove('border-gray-100', 'hover:border-gray-300');
                        indicator.classList.remove('opacity-0');
                        indicatorBorder.classList.add('border-zandu-red');
                        indicatorBorder.classList.remove('border-gray-300');
                    } else {
                        label.classList.remove('border-zandu-red', 'bg-red-50');
                        label.classList.add('border-gray-100', 'hover:border-gray-300');
                        indicator.classList.add('opacity-0');
                        indicatorBorder.classList.remove('border-zandu-red');
                        indicatorBorder.classList.add('border-gray-300');
                    }
                });
            }

            function formatPrice(value) {
                return new Intl.NumberFormat('pt-AO', {
                    style: 'currency',
                    currency: 'AOA',
                    minimumFractionDigits: 2
                }).format(value).replace('AOA', 'Kz');
            }

            function updateTotals() {
                const grandTotal = Math.max(0, subTotal + currentShippingRate - currentDiscount);

                shippingValueEl.textContent = currentShippingRate > 0 ? formatPrice(currentShippingRate) : '--';
                
                if (currentDiscount > 0) {
                    discountValueEl.textContent = `- ${formatPrice(currentDiscount)}`;
                    discountRow.classList.remove('hidden');
                } else {
                    discountRow.classList.add('hidden');
                }
                
                totalValueEl.textContent = formatPrice(grandTotal);

                // Atualiza o valor total nas instruções de pagamento se necessário
                const checkedPayment = document.querySelector('input[name="payment_method_id"]:checked');
                if (checkedPayment) {
                    updatePaymentInstructions(null, checkedPayment);
                }
            }

            // --- Lógica de Pagamento ---

            function handlePaymentChange() {
                updateRadioStyles(paymentRadios); // Atualiza visual
                
                const selectedRadio = document.querySelector('input[name="payment_method_id"]:checked');
                if (!selectedRadio) return;

                const details = JSON.parse(selectedRadio.getAttribute('data-details'));
                const isCashOnDelivery = details.type === 'cash_on_delivery';

                if (isCashOnDelivery) {
                    comprovativoGroup.classList.add('hidden');
                    comprovativoInput.required = false;
                    comprovativoInput.disabled = true;
                    submitBtn.innerHTML = 'Confirmar Pedido <i class="ph-bold ph-check ml-2"></i>';
                } else {
                    comprovativoGroup.classList.remove('hidden');
                    comprovativoInput.required = true;
                    comprovativoInput.disabled = false;
                    submitBtn.innerHTML = 'Enviar Comprovativo e Finalizar <i class="ph-bold ph-paper-plane-right ml-2"></i>';
                }

                updatePaymentInstructions(details);
            }

            function updatePaymentInstructions(details = null, radioElement = null) {
                if (!details && radioElement) {
                    details = JSON.parse(radioElement.getAttribute('data-details'));
                }
                if (!details) return;

                const isCashOnDelivery = details.type === 'cash_on_delivery';
                
                let html = `<h3 class="font-bold text-blue-900 mb-2 flex items-center gap-2"><i class="ph-fill ph-info"></i> Instruções: ${details.name}</h3>`;
                
                if (details.instructions) {
                    html += `<p class="text-sm text-blue-800 mb-3 leading-relaxed">${details.instructions.replace(/\n/g, '<br>')}</p>`;
                }

                if (!isCashOnDelivery) {
                    html += '<ul class="text-sm text-blue-800 bg-white/50 rounded-lg p-3 space-y-1 border border-blue-100">';
                    if (details.type === 'bank_transfer') {
                        if (details.bank_account_holder) html += `<li><span class="font-bold">Titular:</span> ${details.bank_account_holder}</li>`;
                        if (details.bank_account_number) html += `<li><span class="font-bold">Conta:</span> ${details.bank_account_number}</li>`;
                        if (details.bank_iban) html += `<li><span class="font-bold">IBAN:</span> ${details.bank_iban}</li>`;
                    } else if (details.type === 'express') {
                        if (details.phone_number) html += `<li><span class="font-bold">Nº Telemóvel:</span> ${details.phone_number}</li>`;
                    }
                    html += '</ul>';
                }
                
                const currentTotalFormatted = totalValueEl.textContent;
                const labelText = isCashOnDelivery ? 'Total a pagar na entrega:' : 'Total a transferir:';

                html += `
                    <div class="mt-4 pt-3 border-t border-blue-200 flex justify-between items-center">
                        <span class="text-sm font-bold text-blue-900">${labelText}</span>
                        <span class="text-lg font-extrabold text-zandu-red">${currentTotalFormatted}</span>
                    </div>
                `;

                instructionsEl.innerHTML = html;
                instructionsEl.classList.remove('hidden');
            }

            // --- Lógica de Cupão ---

            async function handleCouponApply() {
                const code = couponInput.value.trim().toUpperCase();
                if (!code) return;

                couponBtn.disabled = true;
                couponBtn.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';
                couponMsg.textContent = '';
                couponMsg.className = 'text-xs mt-2 block font-medium'; // Reset classes
                couponHiddenInput.value = '';

                try {
                    const response = await fetch(`/payment/${cartUuid}/apply-coupon`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ coupon_code: code })
                    });

                    const data = await response.json();

                    if (!response.ok) throw new Error(data.message || 'Erro ao aplicar cupão.');

                    currentDiscount = data.discountAmount;
                    couponMsg.textContent = data.message;
                    couponMsg.classList.add('text-green-600');
                    couponHiddenInput.value = code;
                    
                } catch (error) {
                    currentDiscount = 0;
                    couponMsg.textContent = error.message;
                    couponMsg.classList.add('text-red-500');
                } finally {
                    updateTotals();
                    couponBtn.disabled = false;
                    couponBtn.textContent = 'Aplicar';
                }
            }

            // --- Event Listeners ---

            // 1. Mudança de Frete (Radio Buttons)
            shippingRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    updateRadioStyles(shippingRadios); // Atualiza visual
                    currentShippingRate = parseFloat(this.getAttribute('data-rate') || 0);
                    updateTotals();
                });
            });

            // 2. Mudança de Pagamento (Radio Buttons)
            paymentRadios.forEach(radio => {
                radio.addEventListener('change', handlePaymentChange);
            });

            // 3. Cupão
            couponBtn.addEventListener('click', handleCouponApply);
            couponInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleCouponApply();
                }
            });

            // Inicialização (caso haja old input ou refresh)
            const checkedShipping = document.querySelector('input[name="shipping_rate_id"]:checked');
            if (checkedShipping) {
                updateRadioStyles(shippingRadios);
                currentShippingRate = parseFloat(checkedShipping.getAttribute('data-rate') || 0);
            }

            const checkedPayment = document.querySelector('input[name="payment_method_id"]:checked');
            if (checkedPayment) {
                handlePaymentChange();
            }
            
            updateTotals();
        });
    </script>
</body>
</html>