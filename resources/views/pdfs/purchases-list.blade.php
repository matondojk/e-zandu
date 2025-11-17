<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Compras</title>
    <style>
        /* Seus estilos CSS */
        @page { 
            margin: 20px 30px; 
            /* ✅ Garantir que o PurchaseResource está definindo o Paper como 'landscape' */
        }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; /* Reduzindo fonte para caber */ color: #333; }
        
        .header-table { width: 100%; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .logo { max-height: 60px; max-width: 200px; }
        .company-info { text-align: right; line-height: 1.4; }
        .title-row { text-align: center; margin-top: 15px; margin-bottom: 15px; }
        .report-title { font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }

        .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .data-table th { background-color: #333; color: #fff; padding: 6px; /* Menor padding */ text-align: left; font-weight: bold; font-size: 9px; /* Menor fonte no header */ text-transform: uppercase; }
        .data-table td { border-bottom: 1px solid #ddd; padding: 6px; vertical-align: middle; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
        
        .data-table tfoot td { padding: 6px 8px; font-weight: bold; background-color: #f0f0f0; }
        .data-table tfoot .total-label { text-align: right; }
        .data-table tfoot .total-value { text-align: right; }
        .data-table tfoot .grand-total { font-size: 1.1em; border-top: 2px solid #000; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 6px; border-radius: 3px; color: #fff; font-size: 9px; font-weight: bold; }
        .bg-green { background-color: #16a34a; color: white; }
        .bg-gray { background-color: #6b7280; color: white; }
        .bg-orange { background-color: #d97706; color: white; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 9px; text-align: center; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td valign="top">
                @if($settings && $settings->logo_dark_path)
                    <img src="{{ public_path($settings->logo_dark_path) }}" class="logo" alt="Logo">
                @else
                    <h2>{{ $settings->store_name ?? 'E-Zandu' }}</h2>
                @endif
            </td>
            <td valign="top" class="company-info">
                <strong>{{ $settings->store_name ?? 'E-Zandu' }}</strong><br>
                NIF: {{ $settings->nif ?? 'N/A' }}<br>
                Tel: {{ $settings->admin_phone ?? 'N/A' }}<br>
                Email: {{ $settings->support_email ?? 'N/A' }}
            </td>
        </tr>
    </table>

    <div class="title-row">
        <div class="report-title">Relatório de Compras</div>
        <small>Gerado em: {{ date('d/m/Y H:i') }}</small>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>Nº Compra</th>
                <th>Fornecedor</th>
                <th>Emissão</th>
                <th>Status</th>
                <th>Entregue</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Desconto</th>
                <th class="text-right">Frete</th>
                <th class="text-right">IVA (14%)</th>
                <th class="text-right">Total Geral</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                <td><strong>{{ $record->purchase_number }}</strong></td>
                <td>{{ $record->supplier->name ?? '-' }}</td>
                <td>{{ $record->issue_date?->format('d/m/Y') }}</td>
                <td>
                    <span class="badge {{ $record->status == 'Recebida' ? 'bg-green' : ($record->status == 'Aberta' ? 'bg-gray' : 'bg-orange') }}">
                        {{ strtoupper($record->status) }}
                    </span>
                </td>
                <td>{{ $record->is_delivered ? 'Sim' : 'Não' }}</td>
                
                <td class="text-right">{{ number_format($record->total_amount, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($record->discount_total, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($record->freight_cost, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($record->iva_amount, 2, ',', '.') }}</td>
                
                <td class="text-right">
                    <strong>{{ number_format($record->total_payable, 2, ',', '.') }} Kz</strong>
                </td>
            </tr>
            @endforeach
        </tbody>

        <tfoot>
            @php $colspan = 9; @endphp <tr>
                <td colspan="{{ $colspan }}" class="total-label">Subtotal (Itens):</td>
                <td class="total-value">{{ number_format($records->sum('total_amount'), 2, ',', '.') }} Kz</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" class="total-label">Total Descontos:</td>
                <td class="total-value" style="color: red;">- {{ number_format($records->sum('discount_total'), 2, ',', '.') }} Kz</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" class="total-label">Total Frete:</td>
                <td class="total-value">+ {{ number_format($records->sum('freight_cost'), 2, ',', '.') }} Kz</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" class="total-label">Total IVA (14%):</td>
                <td class="total-value">+ {{ number_format($records->sum('iva_amount'), 2, ',', '.') }} Kz</td>
            </tr>
            <tr class="grand-total">
                <td colspan="{{ $colspan }}" class="total-label">TOTAL GERAL:</td>
                <td class="total-value">{{ number_format($records->sum('total_payable'), 2, ',', '.') }} Kz</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Processado por {{ config('app.name') }} | Página <script type="text/php">if (isset($pdf)) { echo $pdf->get_page_number(); }</script>
    </div>

</body>
</html>