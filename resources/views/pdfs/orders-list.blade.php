<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Vendas</title>
    <style>
        @page {
            margin: 2cm; /* Margens da página para impressão */
            size: A4 landscape; /* Página deitada */
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 10px; /* Tamanho menor para relatório */
            line-height: 1.3;
        }
        .container {
            width: 100%;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            border-bottom: 1px solid #555;
            padding-bottom: 5px;
            margin-bottom: 0;
        }
        .header-info {
            text-align: center;
            font-size: 11px;
            margin-bottom: 20px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th, .report-table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        .report-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .report-table td.text-right, .report-table th.text-right {
            text-align: right;
        }
        .currency {
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        .status-paid { color: green; }
        .status-active { color: orange; }
        .summary {
            margin-top: 20px;
            width: 30%;
            float: right;
            border: 1px solid #ccc;
        }
        .summary td {
            padding: 8px;
        }
        .summary .label {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .footer {
            position: fixed;
            bottom: -60px; /* Ajuste conforme a margem da página */
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="footer">
        {{ $settings->company_name ?? 'E-Zandu' }} | Gerado em: {{ date('d/m/Y H:i') }}
    </div>

    <div class="container">
        <h1>Relatório de Vendas</h1>
        <p class="header-info">
            {{ $settings->company_name ?? 'E-Zandu' }}<br>
            Período do Relatório (Baseado nos filtros aplicados)
        </p>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Nº Pedido</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Telefone</th>
                    <th>Status Pag.</th>
                    <th>Método Pag.</th>
                    <th>Entrega</th>
                    <th class="text-right">Subtotal</th>
                    <th class="text-right">Frete</th>
                    <th class="text-right">Desconto</th>
                    <th class="text-right">Total Geral</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalGeralRelatorio = 0;
                @endphp

                @forelse($records as $record)
                    @php
                        $grandTotal = ($record->total_price + $record->shipping_amount) - $record->discount_amount;
                        $totalGeralRelatorio += $grandTotal;
                    @endphp
                    <tr>
                        <td>{{ $record->cart_number ?? $record->id }}</td>
                        <td>{{ $record->created_at->format('d/m/Y') }}</td>
                        <td>{{ $record->user?->name ?? 'Anónimo' }}</td>
                        <td>{{ $record->user?->phone ?? '-' }}</td>
                        <td>
                            @if($record->status == 'paid' || $record->status == 'completed')
                                <span class="status-paid">Pago</span>
                            @else
                                <span class="status-active">Pendente</span>
                            @endif
                        </td>
                        <td>{{ $record->paymentMethod?->name ?? '-' }}</td>
                        <td>{{ $record->shippingRate?->province_name ?? '-' }}</td>
                        <td class="text-right">{{ number_format($record->total_price, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($record->shipping_amount, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($record->discount_amount, 2, ',', '.') }}</td>
                        <td class="text-right"><strong>{{ number_format($grandTotal, 2, ',', '.') }} <span class="currency">Kz</span></strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center;">Nenhum pedido encontrado para os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Sumário -->
        <table class="summary">
            <tr>
                <td class="label">Total de Pedidos</td>
                <td class="text-right">{{ $records->count() }}</td>
            </tr>
            <tr>
                <td class="label">Valor Total (Kz)</td>
                <td class="text-right"><strong>{{ number_format($totalGeralRelatorio, 2, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>