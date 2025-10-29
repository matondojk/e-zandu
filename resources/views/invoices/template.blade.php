<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fatura Pedido #{{ $cart->id }}</title>
    <style>
        /* Estilos básicos para o PDF */
        body {
            font-family: DejaVu Sans, sans-serif; /* DejaVu Sans suporta caracteres especiais */
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container { width: 95%; margin: 0 auto; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-left { float: left; width: 70%; }
        .header-right { float: right; width: 30%; text-align: right; }
        .header h1 { margin: 0 0 5px 0; color: #000; }
        .logo { max-width: 150px; max-height: 50px; }
        .details { margin-bottom: 25px; padding-bottom: 10px; }
        .details-left { float: left; width: 50%; }
        .details-right { float: right; width: 45%; text-align: right; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-table { width: 40%; float: right; border: 1px solid #000; }
        .total-table td { border: none; }
        .total-table .label { text-align: right; font-weight: bold; }
        .total-table .value { text-align: right; width: 120px; }
        .total-table .grand-total { background-color: #e6f7f5; border-top: 2px solid #128C7E; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #777; }
        
        /* ✅ CSS PARA OS NOVOS STATUS */
        .status-badge {
            display: inline-block; padding: 4px 8px; border-radius: 4px; color: #fff; font-weight: bold;
        }
        .status-paid { background-color: #28a745; } /* Verde para Pago */
        .status-pending { background-color: #ffc107; color: #000; } /* Amarelo para Pendente */
        .status-processing { background-color: #ffc107; color: #000; } /* Amarelo para Em Processamento */
        .status-delivered { background-color: #007bff; } /* Azul para Entregue */
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                @if($logoBase64)
                    <img src="data:{{ $logoMime }};base64,{{ $logoBase64 }}" class="logo" alt="Logo da Loja">
                @endif
                <h1>{{ $settings->store_name ?? 'TangaWA Shop' }}</h1>
                <p style="margin: 0;">NIF: {{ $settings->nif ?? 'N/A' }}</p>
            </div>
            <div class="header-right">
                <h2 style="margin: 0; color: #128C7E;">FATURA</h2>
            </div>
            <div class="clear"></div>
        </div>

        <div class="details">
            <div class="details-left">
                <strong>Detalhes do Cliente:</strong><br>
                Nome: {{ $cart->customer->name ?? 'N/A' }}<br>
                Telefone (WhatsApp): {{ $cart->customer->phone ?? $cart->user_phone }}<br>
                Endereço de Entrega: {{ $cart->customer->delivery_address ?? 'Não fornecido' }}
            </div>
            <div class="details-right">
                <strong>Pedido Nº:</strong> {{ $cart->id }}<br>
                <strong>Data de Emissão:</strong> {{ $issueDate }}<br>
                
                <strong>Status Pagamento:</strong> 
                @switch($cart->status)
                    @case('paid')
                        <span class="status-badge status-paid">PAGO</span>
                        @break
                    @case('active')
                        <span class="status-badge status-pending">PENDENTE</span>
                        @break
                    @default
                        {{ ucfirst($cart->status) }}
                @endswitch
                <br>
                
                <strong>Status Entrega:</strong> 
                @switch($cart->delivery_status)
                    @case('processing')
                        <span class="status-badge status-processing">EM PROCESSAMENTO</span>
                        @break
                    @case('delivered')
                        <span class="status-badge status-delivered">ENTREGUE</span>
                        @break
                    @case('pending')
                    @default
                        PENDENTE
                @endswitch
            </div>
            <div class="clear"></div>
        </div>

        <h3>Itens do Pedido</h3>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qtd.</th>
                    <th>Preço Unit. (Kz)</th>
                    <th>Total (Kz)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cart->items as $item)
                    <tr>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 2, ',', '.') }}</td>
                        <td>{{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-table">
            <table>
                <tr>
                    <td class="label">Subtotal dos Produtos:</td>
                    <td class="value">{{ number_format($cart->total_price, 2, ',', '.') }} Kz</td>
                </tr>
                <tr>
                    <td class="label">Taxa de Frete:</td>
                    <td class="value">{{ number_format($shippingRate, 2, ',', '.') }} Kz</td>
                </tr>
                <tr>
                    <td class="label">Impostos (IVA 14%):</td>
                    <td class="value">0.00 Kz</td> 
                </tr>
                <tr class="grand-total">
                    <td class="label">TOTAL GERAL:</td>
                    <td class="value" style="font-size: 16px;">
                        {{ number_format($grandTotal, 2, ',', '.') }} Kz
                    </td>
                </tr>
            </table>
        </div>
        <div class="clear"></div>

        <div class="footer">
            <p>Obrigado pela sua compra!</p>
            <p>Esta fatura confirma o pagamento e os detalhes do seu pedido.</p>
        </div>
    </div>
</body>
</html>