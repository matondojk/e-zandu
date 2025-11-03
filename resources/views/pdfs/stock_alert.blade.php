<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Relatório de Stock Baixo' }}</title>
    <style>
        /* Estilos básicos para o PDF */
        body {
            font-family: DejaVu Sans, sans-serif; /* Suporta caracteres especiais */
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 95%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #b00000; /* Vermelho para alerta */
            font-size: 24px;
        }
        .details {
            margin-bottom: 25px;
            padding-bottom: 10px;
        }
        .details p {
            margin: 5px 0;
        }
        .clear {
            clear: both;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table thead tr {
            background-color: #f2f2f2;
            border-bottom: 2px solid #ddd;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        table th {
            font-weight: bold;
            color: #555;
        }
        /* Coluna de Stock Atual com destaque */
        table td.stock-level {
            font-weight: bold;
            text-align: center;
        }
        /* Destaque para stock muito baixo (ex: 0 ou negativo) */
        table td.stock-critical {
            color: #b00000; 
            font-weight: bold;
            text-align: center;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Alerta de Stock Baixo</h1>
        </div>

        <div class="details">
            <p><strong>Data do Relatório:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Nível Mínimo Configurado:</strong> {{ $minimumStock }} unidades</p>
            <p>O sistema detetou <strong>{{ $products->count() }}</strong> produtos abaixo do nível mínimo de stock.</p>
        </div>

        <div class="clear"></div>

        <table>
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>SKU</th>
                    <th style="text-align: center;">Stock Atual</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku ?? 'N/A' }}</td>
                        
                        {{-- Define a classe CSS com base no stock --}}
                        @php
                            $stockClass = ($product->stock_atual <= 0) ? 'stock-critical' : 'stock-level';
                        @endphp
                        
                        <td class="{{ $stockClass }}">
                            {{ (int)$product->stock_atual }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center;">Nenhum produto encontrado abaixo do stock mínimo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Este é um relatório automático gerado por {{ config('app.name') }}.</p>
        </div>
    </div>
</body>
</html>
