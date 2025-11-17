<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detalhes do Armazém {{ $record->name }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; line-height: 1.5; }
        .container { width: 100%; margin: 0 auto; }
        .header-table { width: 100%; border-bottom: 1px solid #eee; margin-bottom: 20px; }
        .header-table td { padding: 10px 0; vertical-align: top; }
        .company-details { text-align: right; }
        h1 { font-size: 24px; color: #000; margin-bottom: 5px; text-align: center; }
        .info-section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .info-section h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 0; margin-bottom: 10px; }
        .details-grid { width: 100%; border-collapse: collapse; }
        .details-grid td { padding: 8px 0; border: none; }
        .details-grid .label { font-weight: bold; width: 35%; padding-right: 10px; vertical-align: top; }
        .stock-items-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .stock-items-table th, .stock-items-table td { border: 1px solid #ccc; padding: 6px; text-align: left; font-size: 11px; }
        .stock-items-table th { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    <h2 style="margin:0; font-size: 16px;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                </td>
                <td class="company-details">
                    Armazém: <strong>{{ $record->name }}</strong><br>
                    Total de Itens: **{{ number_format($record->movements()->sum('quantity'), 0, ',', '.') }}**<br>
                    Atualizado em: {{ $record->updated_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>

        <h1>Detalhes do Armazém</h1>

        <div class="info-section">
            <h2>Informações Principais</h2>
            <table class="details-grid">
                <tr>
                    <td class="label">Nome do Armazém:</td>
                    <td><strong style="font-size: 1.1rem;">{{ $record->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Armazém Padrão:</td>
                    <td>{{ $record->is_default ? 'Sim' : 'Não' }}</td>
                </tr>
                <tr>
                    <td class="label">Localização:</td>
                    <td>{{ $record->location ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        
        <div class="info-section">
            <h2>Itens Atualmente em Estoque</h2>
            
            @php
                // Esta query encontra o saldo final de cada produto neste armazém
                // Você pode precisar carregar a relação 'movements' no getEloquentQuery se a performance for lenta.
                $items = \App\Models\StockMovement::query()
                    ->where('stock_id', $record->id)
                    ->join('products', 'stock_movements.product_id', '=', 'products.id')
                    ->groupBy('product_id', 'products.name', 'products.sku', 'products.unit')
                    ->select('products.name', 'products.sku', 'products.unit', \Illuminate\Support\Facades\DB::raw('SUM(stock_movements.quantity) as current_quantity'))
                    ->having('current_quantity', '!=', 0) // Mostra apenas itens com estoque > 0 ou < 0
                    ->get();
            @endphp
            
            @if($items->isNotEmpty())
                <table class="stock-items-table">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>SKU</th>
                            <th>Unidade</th>
                            <th style="text-align: right;">Qtd. Atual</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->sku ?? '-' }}</td>
                                <td>{{ $item->unit ?? '-' }}</td>
                                <td style="text-align: right; font-weight: bold; color: {{ $item->current_quantity > 0 ? 'green' : 'red' }};">
                                    {{ number_format($item->current_quantity, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Este armazém está vazio (ou não possui movimentos de estoque registados).</p>
            @endif
        </div>
    </div>
</body>
</html>