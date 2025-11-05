<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pedido #{{ $cart->id }}</title>
    <style>
        :root {
            --primary-color: #128C7E;
            --secondary-color: #25D366;
            --background-color: #f4f7f6;
            --card-background: #ffffff;
            --text-color: #333;
            --text-light: #555;
            --border-color: #e0e0e0;
            --error-color: #D9534F;
            --success-color: #5CB85C;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            color: var(--text-color);
        }
        .container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        .form-container, .summary-container {
            background: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.05);
            padding: 30px;
            border: 1px solid var(--border-color);
        }
        .form-container {
            flex: 2;
            min-width: 300px;
        }
        .summary-container {
            flex: 1;
            min-width: 280px;
            position: sticky;
            top: 20px;
        }
        h1, h2, h3 {
            color: var(--primary-color);
            margin-top: 0;
        }
        h1 {
            text-align: center;
            margin-bottom: 25px;
        }
        h2 {
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            font-size: 1.5em;
        }
        h3 {
            font-size: 1.2em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.95em;
        }
        .form-group input[type="text"],
        .form-group input[type="file"],
        .form-group textarea,
        .form-group select { /* <-- Corrigi um typo aqui de .form-sgroup */
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(18, 140, 126, 0.1);
            outline: none;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .btn {
            background-color: var(--secondary-color);
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s, transform 0.1s;
        }
        .btn:hover {
            background-color: #20b355;
        }
        .btn:active {
            transform: scale(0.99);
        }
        .error {
            color: var(--error-color);
            font-size: 0.9em;
            margin-top: 5px;
        }
        
        /* Resumo do Pedido */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .summary-table td {
            padding: 12px 0;
        }
        .summary-table .label {
            text-align: left;
            color: var(--text-light);
        }
        .summary-table .value {
            text-align: right;
            font-weight: 600;
        }
        .summary-table .separator {
            border-top: 1px dashed var(--border-color);
        }
        .summary-table .grand-total td {
            font-size: 1.3em;
            font-weight: bold;
            color: var(--primary-color);
            padding-top: 15px;
            border-top: 2px solid var(--primary-color);
        }
        #discount-row .value {
            color: var(--success-color);
        }

        /* Cupão */
        .coupon-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .coupon-group input {
            flex: 1;
        }
        .coupon-group button {
            flex: 0 0 auto;
            padding: 0 20px;
            background-color: var(--primary-color);
            border-radius: 8px;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .coupon-group button:hover {
            background-color: #0e665c;
        }
        .coupon-group button:disabled {
            background-color: #aaa;
            cursor: not-allowed;
        }
        #coupon-message {
            font-size: 0.9em;
            margin-top: 10px;
            display: block;
        }
        #coupon-message.success { color: var(--success-color); }
        #coupon-message.error { color: var(--error-color); }

        /* Instruções de Pagamento */
        .instrucoes {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: #fdfdfd;
        }
        .instrucoes ul {
            list-style-type: none;
            padding-left: 0;
            margin: 10px 0;
        }
        .instrucoes li {
            margin-bottom: 8px;
            font-size: 0.95em;
            color: var(--text-light);
        }
        .instrucoes li strong {
            color: var(--text-color);
        }
        .instrucoes .total-label {
            font-size: 1.1em;
            font-weight: 600;
            color: var(--text-color);
            border-top: 1px dashed var(--border-color);
            padding-top: 15px;
            margin-top: 15px;
        }
        .instrucoes .total-label span {
            color: var(--primary-color);
            font-weight: 700;
        }

        /* Responsividade */
        @media (max-width: 800px) {
            .container {
                flex-direction: column-reverse;
            }
            .summary-container {
                position: static;
                width: 100%;
                box-sizing: border-box;
            }
        }
    </style>
</head>

<body>
    
    <div class="container">

        <main class="form-container">
            <h1>Finalizar Pedido</h1>
            <p style="text-align: center; margin-top: -20px; margin-bottom: 25px; color: var(--text-light);">Pedido #{{ $cart->id }}</p>

            <form action="{{ route('payment.store', $cart->uuid) }}" method="POST" enctype="multipart/form-data" id="payment-form">
                @csrf

                <h2>1. Detalhes de Entrega</h2>
                
                <div class="form-group">
                    <label for="shipping-select">Província de Entrega (Obrigatório):</label>
                    <select id="shipping-select" name="shipping_rate_id" required>
                        <option value="" data-rate="0">Selecione a província...</option>
                        @foreach ($shippingRates as $rate)
                            <option value="{{ $rate->id }}" data-rate="{{ $rate->rate }}">
                                {{ $rate->province_name }} ({{ $orderService->formatPrice($rate->rate) }})
                            </option>
                        @endforeach
                    </select>
                    @error('shipping_rate_id') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="delivery_address">Referência de Entrega Completa (Obrigatório):</label>
                    <textarea id="delivery_address" name="delivery_address" rows="3" placeholder="Ex: Pedonal Azul, Villa de Cacuaco, Prédio 123, Apt 10" required>{{ old('delivery_address', $userAddress) }}</textarea>
                    @error('delivery_address') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group">
                    <label for="order_notes">Notas (Ex: Tamanho, Cor, etc.)</label>
                    <textarea id="order_notes" name="order_notes" rows="2" placeholder="Alguma preferência de tamanho, cor ou outra observação?">{{ old('order_notes') }}</textarea>
                </div>


                <h2>2. Pagamento</h2>

                <div class="form-group">
                    <label for="payment-select">Método de Pagamento (Obrigatório):</label>
                    <select id="payment-select" name="payment_method_id" required>
                        <option value="">Selecione como deseja pagar...</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}" data-details="{{ $method->toJson() }}">
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method_id') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div id="payment-instructions" class="instrucoes" style="display: none;">
                    </div>

                <div class="form-group" id="comprovativo-group" style="display: none; margin-top: 20px;">
                    <label for="comprovativo">Anexar Comprovativo (Obrigatório):</label>
                    <input type="file" id="comprovativo" name="comprovativo" accept="image/*,.pdf">
                    @error('comprovativo') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <button type="submit" class="btn" id="submit-btn">Confirmar Pedido</button>
                </div>
            </form>
        </main>

        <aside class="summary-container">
            <h2>Resumo do Pedido</h2>
            <p><strong>Cliente:</strong> {{ $userName }}</p>

            <table class="summary-table">
                <tr>
                    <td class="label">Subtotal dos Produtos:</td>
                    <td class="value">{{ $subTotalFormatted }}</td>
                </tr>
                <tr>
                    <td class="label">Taxa de Frete:</td>
                    <td class="value" id="shipping-value">{{ $orderService->formatPrice(0) }}</td>
                </tr>
                <tr id="discount-row" style="display: none;">
                    <td class="label">Desconto (Cupão):</td>
                    <td class="value" id="discount-value">- {{ $orderService->formatPrice(0) }}</td>
                </tr>
                <tr class="grand-total separator">
                    <td class="label">TOTAL GERAL:</td>
                    <td class="value" id="total-value">{{ $subTotalFormatted }}</td>
                </tr>
            </table>

            <div class="form-group">
                <label for="coupon_code_input" style="margin-top: 20px;">Cupão de Desconto</label>
                <div class="coupon-group">
                    <input type="text" id="coupon_code_input" placeholder="Insira o seu código">
                    <button type="button" id="apply-coupon-btn">Aplicar</button>
                </div>
                <input type="hidden" name="coupon_code" id="coupon_code_hidden">
                <span id="coupon-message"></span>
                @error('coupon_code')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>
        </aside>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- Variáveis Globais e Constantes ---
            const subTotal = {{ $subTotalRaw }};
            const cartUuid = '{{ $cart->uuid }}';

            // =================================================================
            // A CORREÇÃO ESTÁ AQUI
            // =================================================================
            // O Laravel usa `_token`, não `_csrf`. Este era o erro.
            const csrfToken = document.querySelector('input[name="_token"]').value;
            // =================================================================
            
            let currentShippingRate = 0;
            let currentDiscount = 0;

            // --- Seletores de Elementos ---
            const shippingSelect = document.getElementById('shipping-select');
            const paymentSelect = document.getElementById('payment-select');
            const couponInput = document.getElementById('coupon_code_input'); // Input visível
            const couponHiddenInput = document.getElementById('coupon_code_hidden'); // Input para o form
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

            // --- Funções Principais ---

            /**
             * Formata um número para a moeda Kz.
             */
            function formatPrice(value) {
                return new Intl.NumberFormat('pt-AO', {
                    style: 'currency',
                    currency: 'AOA',
                    minimumFractionDigits: 2
                }).format(value).replace('AOA', 'Kz');
            }

            /**
             * Atualiza o Resumo do Pedido (Frete, Desconto, Total).
             */
            function updateTotals() {
                const grandTotal = Math.max(0, subTotal + currentShippingRate - currentDiscount); // Garante que não é negativo

                // Atualiza tabela de resumo
                shippingValueEl.textContent = formatPrice(currentShippingRate);
                
                if (currentDiscount > 0) {
                    discountValueEl.textContent = `- ${formatPrice(currentDiscount)}`;
                    discountRow.style.display = 'table-row';
                } else {
                    discountRow.style.display = 'none';
                }
                
                totalValueEl.textContent = formatPrice(grandTotal);

                // Atualiza as instruções de pagamento, se visíveis
                if (paymentSelect.value) {
                    updatePaymentInstructions();
                }
            }

            /**
             * Controla a exibição dos campos com base no Método de Pagamento.
             */
            function handlePaymentMethodChange() {
                const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                if (!selectedOption.value) {
                    instructionsEl.style.display = 'none';
                    comprovativoGroup.style.display = 'none';
                    return;
                }

                const details = JSON.parse(selectedOption.getAttribute('data-details'));
                const isCashOnDelivery = details.type === 'cash_on_delivery';

                if (isCashOnDelivery) {
                    // Esconde comprovativo, torna não-obrigatório
                    comprovativoGroup.style.display = 'none';
                    comprovativoInput.required = false;
                    comprovativoInput.disabled = true;
                    submitBtn.textContent = 'Confirmar Pedido (Pagar na Entrega)';
                } else {
                    // Mostra comprovativo, torna obrigatório
                    comprovativoGroup.style.display = 'block';
                    comprovativoInput.required = true;
                    comprovativoInput.disabled = false;
                    submitBtn.textContent = 'Confirmar Pagamento e Envio';
                }

                updatePaymentInstructions(details);
            }

            /**
             * Constrói e exibe o bloco de instruções de pagamento.
             */
            function updatePaymentInstructions(details = null) {
                if (!details) {
                    const selectedOption = paymentSelect.options[paymentSelect.selectedIndex];
                    if (!selectedOption.value) return;
                    details = JSON.parse(selectedOption.getAttribute('data-details'));
                }

                const isCashOnDelivery = details.type === 'cash_on_delivery';
                
                let html = `<h3>Instruções para ${details.name}</h3>`;
                
                if (details.instructions) {
                    html += `<p>${details.instructions.replace(/\n/g, '<br>')}</p>`;
                }

                if (!isCashOnDelivery) {
                    html += '<ul>';
                    if (details.type === 'bank_transfer') {
                        if (details.bank_account_holder) html += `<li><strong>Titular:</strong> ${details.bank_account_holder}</li>`;
                        if (details.bank_account_number) html += `<li><strong>Nº Conta:</strong> ${details.bank_account_number}</li>`;
                        if (details.bank_iban) html += `<li><strong>IBAN:</strong> ${details.bank_iban}</li>`;
                    } else if (details.type === 'express') {
                        if (details.phone_number) html += `<li><strong>Nº Express:</strong> ${details.phone_number}</li>`;
                    }
                    html += '</ul>';
                }
                
                // Label e Valor Total Dinâmicos
                const totalLabel = isCashOnDelivery ? 'Valor Exato a Pagar na Entrega:' : 'Valor Exato a Transferir:';
                const currentTotalFormatted = totalValueEl.textContent;

                html += `<p class="total-label">${totalLabel} <span>${currentTotalFormatted}</span></p>`;

                if (!isCashOnDelivery) {
                     html += `<p style="font-size: 0.9em; color: var(--text-light); margin-top: 10px;"><i>* Por favor, anexe o comprovativo após a transferência.</i></p>`;
                }

                instructionsEl.innerHTML = html;
                instructionsEl.style.display = 'block';
            }

            /**
             * Lida com a aplicação do cupão via AJAX.
             */
            async function handleCouponApply() {
                const code = couponInput.value.trim().toUpperCase();
                if (!code) return;

                couponBtn.disabled = true;
                couponBtn.textContent = '...';
                couponMsg.textContent = '';
                couponMsg.className = '';
                couponHiddenInput.value = ''; // Limpa o cupão anterior

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

                    if (!response.ok) {
                        throw new Error(data.message || 'Erro ao aplicar cupão.');
                    }

                    // Sucesso
                    currentDiscount = data.discountAmount;
                    couponMsg.textContent = data.message;
                    couponMsg.className = 'success';
                    couponInput.value = code; // Mantém o código
                    couponHiddenInput.value = code; // Adiciona ao campo hidden para o form
                    
                } catch (error) {
                    // Erro
                    currentDiscount = 0;
                    couponMsg.textContent = error.message;
                    couponMsg.className = 'error';
                } finally {
                    // Re-calcula totais e reativa o botão
                    updateTotals();
                    couponBtn.disabled = false;
                    couponBtn.textContent = 'Aplicar';
                }
            }


            // --- Event Listeners Iniciais ---

            // 1. Mudar Frete
            shippingSelect.addEventListener('change', function() {
                const selectedOption = shippingSelect.options[shippingSelect.selectedIndex];
                currentShippingRate = parseFloat(selectedOption.getAttribute('data-rate') || 0);
                updateTotals();
            });

            // 2. Mudar Método de Pagamento
            paymentSelect.addEventListener('change', handlePaymentMethodChange);

            // 3. Aplicar Cupão
            couponBtn.addEventListener('click', handleCouponApply);
            couponInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Impede o submit do formulário
                    handleCouponApply();
                }
            });

        });
    </script>
</body>
</html>