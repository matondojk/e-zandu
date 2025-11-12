<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Pedido #{{ $record->purchase_number }}</title>
    <style>
        @page { margin: 0px; } /* Margem zero para usar faixas coloridas se quiser */
        body { margin: 30px 40px; font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #444; line-height: 1.5; }

        /* Header */
        .header-table { width: 100%; margin-bottom: 40px; }
        .logo { max-height: 70px; max-width: 220px; }
        .invoice-title { font-size: 32px; font-weight: bold; color: #333; text-align: right; text-transform: uppercase; letter-spacing: -1px; }
        .invoice-meta { text-align: right; color: #777; font-size: 11px; margin-top: 5px; }
        .status-badge { 
            background-color: #eee; color: #333; padding: 5px 10px; border-radius: 4px; 
            font-weight: bold; font-size: 10px; text-transform: uppercase; border: 1px solid #ccc;
        }

        /* Info Boxes (From / To) */
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-box { padding: 0; width: 48%; vertical-align: top; }
        .info-title { font-size: 10px; text-transform: uppercase; color: #888; font-weight: bold; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 5px; display: block; }
        .info-content { font-size: 12px; color: #333; }
        .info-content strong { font-size: 13px; color: #000; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background-color: #222; color: #fff; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .items-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .items-table tr:last-child td { border-bottom: 2px solid #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Totals */
        .totals-table { width: 45%; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 6px 0; text-align: right; font-size: 12px; }
        .totals-label { color: #777; padding-right: 15px; }
        .totals-value { color: #333; font-weight: 500; }
        .total-final td { border-top: 2px solid #333; padding-top: 10px; font-size: 16px; font-weight: bold; color: #000; }

        /* Footer */
        .footer-box { margin-top: 50px; border-top: 1px solid #eee; padding-top: 20px; font-size: 10px; color: #999; text-align: center; }
        .notes-box { background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 11px; color: #555; border-left: 4px solid #ddd; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td valign="top">
                @if($settings && $settings->logo_dark_path)
                    <img src="{{ public_path($settings->logo_dark_path) }}" class="logo" alt="Logo">
                @else
                    <h1>{{ $settings->store_name ?? 'E-Zandu' }}</h1>
                @endif
            </td>
            <td valign="top" align="right">
                <div class="invoice-title">Pedido de Compra</div>
                <div class="invoice-meta">
                    <strong>Nº:</strong> {{ $record->purchase_number }}<br>
                    <strong>Data:</strong> {{ $record->issue_date?->format('d/m/Y') }}
                </div>
                <div style="margin-top: 10px;">
                    <span class="status-badge">{{ $record->status }}</span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-box">
                <span class="info-title">De (Comprador):</span>
                <div class="info-content">
                    <strong>{{ $settings->store_name ?? 'E-Zandu' }}</strong><br>
                    NIF: {{ $settings->nif ?? 'N/A' }}<br>
                    Tel: {{ $settings->admin_phone ?? 'N/A' }}<br>
                    Email: {{ $settings->support_email ?? 'N/A' }}
                </div>
            </td>
            
            <td style="width: 4%"></td> <td class="info-box">
                <span class="info-title">Para (Fornecedor):</span>
                <div class="info-content">
                    <strong>{{ $record->supplier->name }}</strong><br>
                    @if($record->supplier->nif) NIF: {{ $record->supplier->nif }}<br> @endif
                    @if($record->supplier->contact_name) Att: {{ $record->supplier->contact_name }}<br> @endif
                    {{ $record->supplier->phone }}<br>
                    {{ $record->supplier->email }}
                </div>
            </td>
        </tr>
    </table>

    <div style="margin-bottom: 20px; font-size: 11px; color: #555;">
        <strong>Local de Entrega:</strong> {{ $record->stock->name }} | 
        <strong>Previsão:</strong> {{ $record->expected_delivery_date?->format('d/m/Y') ?? 'Imediata' }} |
        <strong>Responsável:</strong> {{ $record->user->name ?? 'Sistema' }}
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="40%">Produto / Descrição</th>
                <th class="text-center">Qtd</th>
                <th class="text-right">Preço Unit.</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->name }}</strong>
                    @if($item->notes)
                        <br><span style="font-size: 9px; color: #777;">{{ $item->notes }}</span>
                    @endif
                </td>
                <td class="text-center">{{ number_format($item->quantity, 2, ',', '.') }} {{ $item->unit }}</td>
                <td class="text-right">{{ number_format($item->unit_cost, 2, ',', '.') }} Kz</td>
                <td class="text-right">{{ number_format($item->quantity * $item->unit_cost, 2, ',', '.') }} Kz</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="totals-label">Subtotal</td>
            <td class="totals-value">{{ number_format($record->total_amount, 2, ',', '.') }} Kz</td>
        </tr>
        @if($record->discount_total > 0)
        <tr>
            <td class="totals-label" style="color: #d32f2f;">Desconto</td>
            <td class="totals-value" style="color: #d32f2f;">- {{ number_format($record->discount_total, 2, ',', '.') }} Kz</td>
        </tr>
        @endif
        @if($record->freight_cost > 0)
        <tr>
            <td class="totals-label">Frete</td>
            <td class="totals-value">+ {{ number_format($record->freight_cost, 2, ',', '.') }} Kz</td>
        </tr>
        @endif
        @if($record->iva_amount > 0)
        <tr>
            <td class="totals-label">IVA (14%)</td>
            <td class="totals-value">+ {{ number_format($record->iva_amount, 2, ',', '.') }} Kz</td>
        </tr>
        @endif
        <tr class="total-final">
            <td>TOTAL A PAGAR</td>
            <td>{{ number_format($record->total_payable, 2, ',', '.') }} Kz</td>
        </tr>
    </table>

    @if($record->internal_notes)
    <div class="notes-box">
        <strong>Observações:</strong><br>
        {{ $record->internal_notes }}
    </div>
    @endif

    <div class="footer-box">
        Obrigado pela sua preferência.<br>
        {{ $settings->store_name }} - Documento processado por computador.
    </div>

</body>
</html>