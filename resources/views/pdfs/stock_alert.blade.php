<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Relatório de Stock Baixo</title>
    <style>
        body {
            font-family: sans-serif;
            color: #333;
            font-size: 13px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            max-height: 50px;
            margin-bottom: 10px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
        }

        .date {
            font-size: 11px;
            color: #6b7280;
            margin-top: 5px;
        }

        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        h1 {
            color: #dc2626;
            font-size: 18px;
            margin: 0;
        }

        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-item {
            width: 33%;
            float: left;
            text-align: center;
        }

        .info-label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-top: 5px;
        }

        /* Tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            padding: 10px;
            border-bottom: 2px solid #e5e7eb;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        /* Destaques de Stock */
        .stock-val {
            font-weight: bold;
            text-align: center;
        }

        .critical {
            color: #dc2626;
            background-color: #fef2f2;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
        }

        .warning {
            color: #d97706;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            padding: 10px;
            border-top: 1px solid #f3f4f6;
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="header">
        @if(!empty($setting->logo_dark_path))
        @php
        $path = public_path($setting->logo_dark_path);
        $logoSrc = $setting->logo_dark_path; // Fallback

        if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        @endphp
        <img src="{{ $logoSrc }}" class="logo" alt="Logo">
        @endif

        <div class="date">Relatório gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="alert-box">
        <h1>⚠️ Atenção: Níveis de Stock Críticos Detetados</h1>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <div class="info-label">Total de Produtos Afetados</div>
            <div class="info-value" style="color: #dc2626;">{{ $products->count() }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">Stock Mínimo Configurado</div>
            <div class="info-value">{{ $minimumStock }} un.</div>
        </div>
        <div class="info-item">
            <div class="info-label">Data da Verificação</div>
            <div class="info-value">{{ date('d/m/Y') }}</div>
        </div>
        <div class="clear"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="50%">Produto</th>
                <th width="25%">SKU</th>
                <th width="25%" style="text-align: center;">Stock Atual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td style="font-family: monospace; font-size: 11px;">{{ $product->sku ?? '-' }}</td>

                <td class="stock-val">
                    @if($product->stock_atual <= 0)
                        <span class="critical">{{ (int)$product->stock_atual }}</span>
                        @else
                        <span class="warning">{{ (int)$product->stock_atual }}</span>
                        @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; padding: 20px;">
                    Tudo certo! Nenhum produto encontrado abaixo do limite.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Gerado automaticamente pelo sistema de gestão da {{ $setting->store_name ?? 'Loja' }}.<br>
        Por favor, reabasteça estes itens o mais breve possível para evitar perda de vendas.
    </div>
</body>

</html>