<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pedido {{ $record->cart_number ?? $record->id }}</title>
    <style>
        /* Estilos CSS inline são necessários para o dompdf */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .header-table td {
            padding: 10px 0;
            vertical-align: top;
            border: none;
        }
        .company-details {
            text-align: right;
        }
        .company-details h2 {
            margin: 0;
            font-size: 24px;
            color: #000;
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
        }
        h1 {
            font-size: 28px;
            color: #000;
            margin-bottom: 10px;
            text-align: center;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }
        .info-table th {
            background-color: #f9f9f9;
            width: 25%;
            text-align: left;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table td.text-right, .items-table th.text-right {
            text-align: right;
        }
        .totals-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
        }
        .totals-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .totals-table .label {
            font-weight: bold;
            text-align: right;
        }
        .totals-table .value {
            text-align: right;
            width: 40%;
        }
        .totals-table .grand-total .label,
        .totals-table .grand-total .value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .currency {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Cabeçalho com Logo e Dados da Empresa -->
        <table class="header-table">
            <tr>
                <td>
                    @if($settings && $settings->logo_dark_path)
                        <!-- Nota: dompdf pode ter problemas com SVGs ou URLs remotas. Use um caminho absoluto se possível -->
                        <img src="{{ public_path($settings->logo_dark_path) }}" alt="Logo" class="logo">
                    @else
                        <h2 style="margin:0;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                    @endif
                </td>
                <td class="company-details">
                    <strong>{{ $settings->company_name ?? 'E-Zandu' }}</strong><br>
                    NIF: {{ $settings->company_nif ?? 'N/A' }}<br>
                    {{ $settings->company_address ?? 'Luanda, Angola' }}<br>
                    Email: {{ $settings->company_email ?? '' }}<br>
                    Telefone: {{ $settings->company_phone ?? '' }}
                </td>
            </tr>
        </table>

        <h1>Nota de Encomenda</h1>

        <!-- Informações do Cliente e Pedido -->
        <table class="info-table">
            <tr>
                <th>Cliente</th>
                <td>{{ $record->user?->name ?? 'Cliente Anónimo' }}</td>
                <th>Nº Pedido</th>
                <td><strong>{{ $record->cart_number ?? $record->id }}</strong></td>
            </tr>
            <tr>
                <th>Telefone/WhatsApp</th>
                <td>{{ $record->user?->phone ?? 'N/A' }}</td>
                <th>Data do Pedido</th>
                <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $record->user?->email ?? 'N/A' }}</td>
                <th>Método de Pagamento</th>
                <td>{{ $record->paymentMethod?->name ?? 'N/A' }}</td>
            </tr>
             <tr>
                <th>Local de Entrega</th>
                <td>{{ $record->shippingRate?->province_name ?? 'N/A' }}</td>
                <th>Morada/Referência</th>
                <td>{{ $record->user?->delivery_address ?? 'N/A' }}</td>
            </tr>
        </table>

        <!-- Tabela de Itens -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th class="text-right">Qtd.</th>
                    <th class="text-right">Preço Unit.</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotalItens = 0; @endphp
                @foreach($record->items as $item)
                @php 
                    $itemTotal = $item->quantity * $item->price;
                    $subtotalItens += $itemTotal;
                @endphp
                <tr>
                    <td>
                        {{ $item->product?->name ?? 'Produto Removido' }}
                        @if($item->variation_name)
                            <br><small>({{ $item->variation_name }})</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->price, 2, ',', '.') }} <span class="currency">Kz</span></td>
                    <td class="text-right">{{ number_format($itemTotal, 2, ',', '.') }} <span class="currency">Kz</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tabela de Totais -->
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal (Itens)</td>
                <td class="value">{{ number_format($subtotalItens, 2, ',', '.') }} <span class="currency">Kz</span></td>
            </tr>
            <tr>
                <td class="label">Taxa de Entrega (Frete)</td>
                <td class="value">{{ number_format($record->shipping_amount, 2, ',', '.') }} <span class="currency">Kz</span></td>
            </tr>
            @if($record->discount_amount > 0)
            <tr>
                <td class="label">Desconto (Cupão)</td>
                <td class="value" style="color: red;">- {{ number_format($record->discount_amount, 2, ',', '.') }} <span class="currency">Kz</span></td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label">Total Geral</td>
                @php
                    $grandTotal = ($subtotalItens + $record->shipping_amount) - $record->discount_amount;
                @endphp
                <td class="value">{{ number_format($grandTotal, 2, ',', '.') }} <span class="currency">Kz</span></td>
            </tr>
        </table>

        <div style="clear: both;"></div>

        <!-- Footer -->
        <div class="footer">
            <p>Obrigado pela sua preferência!</p>
            <p>{{ $settings->company_name ?? 'E-Zandu' }} &copy; {{ date('Y') }}</p>
        </div>
    </div>
</body>
</html>