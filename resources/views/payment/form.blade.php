<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Pedido #{{ $cart->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #128C7E;
            margin-bottom: 5px;
        }

        .sub-header {
            text-align: center;
            color: #555;
            margin-bottom: 20px;
            font-size: 14px;
        }

        /* Estilos da Tabela de Detalhes de Preço */
        .detalhes {
            background-color: #e6f7f5;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #128C7E;
            margin-bottom: 20px;
        }

        .detalhes table {
            width: 100%;
            border-collapse: collapse;
        }

        .detalhes td {
            padding: 5px 0;
            border-top: 1px dashed #ccc;
        }

        .detalhes .label {
            text-align: left;
        }

        .detalhes .value {
            text-align: right;
            font-weight: bold;
        }

        .detalhes .grand-total td {
            font-size: 1.5em;
            font-weight: bold;
            color: #128C7E;
            border-top: 2px solid #128C7E;
            padding-top: 10px;
        }

        /* Fim dos Estilos de Detalhes */

        .instrucoes {
            margin-top: 20px;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 5px;
        }

        .instrucoes ul {
            list-style-type: none;
            padding-left: 0;
        }

        .instrucoes li {
            margin-bottom: 8px;
            font-size: 16px;
            color: #333;
        }

        .form-group {
            margin-top: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
        }

        .btn {
            background-color: #25D366;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
            margin-top: 20px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #128C7E;
        }

        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <h1>Confirmação de Pedido</h1>
    <p class="sub-header">Finalize o seu Pedido #{{ $cart->id }}, {{ $customerName }}.</p>

    <div class="detalhes">
        <p><strong>Cliente:</strong> {{ $customerName }}</p>

        <!-- NOVO BLOCO DE DETALHES DE PREÇO -->
        <table>
            <tr>
                <td class="label">Subtotal dos Produtos:</td>
                <td class="value">{{ $subTotal }}</td>
            </tr>
            <tr>
                <td class="label">Taxa de Frete (Entrega):</td>
                <td class="value">{{ $shippingRate }}</td>
            </tr>
            <tr class="grand-total">
                <td class="label">TOTAL GERAL A PAGAR:</td>
                <td class="value">{{ $grandTotal }}</td>
            </tr>
        </table>
        <!-- FIM DO NOVO BLOCO -->

    </div>

    <div class="instrucoes">
        <h3>Instruções de Pagamento</h3>
        <p>1. Efetue a transferência para um dos dados abaixo.</p>
        <p>2. Preencha o seu endereço de entrega.</p>
        <p>3. Anexe o comprovativo e clique em "Confirmar Pagamento".</p>

        <ul>
            <li><strong>IBAN:</strong> {{ $iban ?? 'IBAN não configurado. Contacte o suporte.' }}</li>
            <li><strong>Multicaixa Express (Nº):</strong> {{ $multicaixa_express ?? 'Nº Express não configurado. Contacte o suporte.' }}</li>
            <hr style="border-top: 1px solid #ccc; margin: 10px 0;">
            <!-- VALOR EXATO AGORA É O GRAND TOTAL -->
            <li><strong>Valor Exato a Transferir:</strong> <span style="font-weight: bold;">{{ $grandTotal }}</span></li>
        </ul>
    </div>

    <form action="{{ route('payment.store', $cart->uuid) }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- CAMPO: ENDEREÇO DE ENTREGA -->
        <div class="form-group">
            <label for="delivery_address">Endereço de Entrega Completo (Obrigatório):</label>
            <textarea id="delivery_address" name="delivery_address" rows="3" placeholder="Ex: Rua 123, Bairro Tal, Luanda" required>{{ old('delivery_address', $customerAddress) }}</textarea>
            @error('delivery_address')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="order_notes">Notas (Ex: Tamanho, Cor, etc.)</label>
            <textarea id="order_notes" name="order_notes" rows="3" placeholder="Por favor, especifique o tamanho, cor ou outra preferência para os seus produtos aqui."></textarea>
        </div>

        <div class="form-group">
            <label for="comprovativo">Anexar Comprovativo (PDF ou Imagem):</label>
            <input type="file" id="comprovativo" name="comprovativo" accept="image/*,.pdf" required>
            @error('comprovativo')
            <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Confirmar Pagamento e Envio</button>
        </div>
    </form>
</body>

</html>