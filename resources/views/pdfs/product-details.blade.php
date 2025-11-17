<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detalhes do Produto {{ $record->sku }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; line-height: 1.5; }
        .container { width: 100%; margin: 0 auto; }
        .header-table { width: 100%; border-bottom: 1px solid #eee; margin-bottom: 20px; }
        .header-table td { padding: 10px 0; vertical-align: top; }
        .company-details { text-align: right; }
        .logo { max-width: 150px; max-height: 80px; }
        h1 { font-size: 24px; color: #000; margin-bottom: 5px; text-align: center; }
        .info-section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .info-section h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 0; margin-bottom: 10px; }
        .details-grid { width: 100%; border-collapse: collapse; }
        .details-grid td { padding: 5px 0; border: none; }
        .details-grid .label { font-weight: bold; width: 30%; }
        .currency { font-family: 'Helvetica', 'Arial', sans-serif; }
        .product-image { max-width: 180px; height: auto; border-radius: 5px; float: left; margin-right: 20px; margin-bottom: 10px; }
        .stock-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .stock-table th, .stock-table td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 11px; }
        .stock-table th { background-color: #f2f2f2; }
        .description { clear: both; margin-top: 10px; }
        .tag { display: inline-block; background-color: #f0f0f0; padding: 3px 6px; border-radius: 3px; margin-right: 5px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    @if($settings && $settings->logo_dark_path)
                        <img src="{{ public_path($settings->logo_dark_path) }}" alt="Logo" class="logo">
                    @else
                        <h2 style="margin:0; font-size: 16px;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                    @endif
                </td>
                <td class="company-details">
                    Produto: <strong>{{ $record->name }}</strong><br>
                    SKU: <strong>{{ $record->sku ?? 'N/A' }}</strong><br>
                    Última Atualização: {{ $record->updated_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>

        <h1>Detalhes do Produto</h1>

        <div class="info-section">
            <h2>Informações Chave</h2>
            
            @if($record->image_url)
                <img src="{{ $record->image_url }}" alt="Imagem Principal" class="product-image">
            @endif

            <table class="details-grid">
                <tr>
                    <td class="label">Preço Normal:</td>
                    <td>{{ number_format($record->price, 2, ',', '.') }} <span class="currency">Kz</span></td>
                </tr>
                @if($record->promotional_price > 0)
                <tr>
                    <td class="label">Preço Promoção:</td>
                    <td style="font-weight: bold; color: green;">{{ number_format($record->promotional_price, 2, ',', '.') }} <span class="currency">Kz</span></td>
                </tr>
                @endif
                <tr>
                    <td class="label">Estoque Total:</td>
                    <td>
                        @php $totalGlobal = $record->movements->sum('quantity'); @endphp
                        <span style="font-weight: bold; color: {{ $totalGlobal > 0 ? 'green' : 'red' }};">
                            {{ number_format($totalGlobal, 0, ',', '.') }} {{ $record->unit ?? 'Unidade(s)' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td class="label">Categorias:</td>
                    <td>
                        @foreach($record->categories as $category)
                            <span class="tag">{{ $category->name }}</span>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <td class="label">Marcas:</td>
                    <td>
                        @foreach($record->brands as $brand)
                            <span class="tag" style="background-color: #e0ffe0;">{{ $brand->name }}</span>
                        @endforeach
                    </td>
                </tr>
            </table>

            <div style="clear: both;"></div>

            <h2>Rastreio de Estoque por Armazém</h2>
            @php
                // OBSERVAÇÃO: Esta lógica usa DB::table, que pode ser lenta. 
                // Para manter a consistência com ViewProduct.php, replicamos a query:
                $stocks = DB::table('stock_movements')
                    ->join('stocks', 'stock_movements.stock_id', '=', 'stocks.id')
                    ->where('stock_movements.product_id', $record->id)
                    ->groupBy('stock_movements.stock_id', 'stocks.name', 'stocks.location')
                    ->select('stocks.name', 'stocks.location', DB::raw('SUM(stock_movements.quantity) as total_quantity'))
                    ->get();
            @endphp
            
            @if($stocks->isNotEmpty())
                <table class="stock-table">
                    <thead>
                        <tr><th>Armazém</th><th>Localização</th><th style="text-align: right;">Qtd. Atual</th></tr>
                    </thead>
                    <tbody>
                        @foreach($stocks as $stock)
                            <tr>
                                <td>{{ $stock->name }}</td>
                                <td>{{ $stock->location ?? 'N/A' }}</td>
                                <td style="text-align: right;">{{ number_format($stock->total_quantity, 0, ',', '.') }} {{ $record->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="font-size: 11px; color: #777;">Não há movimentos de estoque registados por armazém.</p>
            @endif
        </div>
        
        <div class="info-section description">
            <h2>Descrições e Variações</h2>
            
            <p style="font-weight: bold;">Descrição Curta:</p>
            <p>{{ $record->short_description }}</p>

            <p style="font-weight: bold; margin-top: 10px;">Descrição Detalhada:</p>
            <p>{!! nl2br(e($record->description)) !!}</p>
            
            @if($record->colors || $record->sizes)
            <p style="font-weight: bold; margin-top: 15px;">Variações:</p>
            <table class="details-grid" style="font-size: 11px;">
                <tr>
                    <td class="label">Cores:</td>
                    <td>
                        @if(is_array($record->colors)) 
                            @foreach($record->colors as $color) <span class="tag">{{ $color }}</span> @endforeach
                        @else 
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Tamanhos:</td>
                    <td>
                        @if(is_array($record->sizes))
                            @foreach($record->sizes as $size) <span class="tag">{{ $size }}</span> @endforeach
                        @else 
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
            @endif
        </div>

        <div class="info-section">
            <h2>Atributos Físicos e Logística</h2>
            <table class="details-grid" style="font-size: 11px;">
                <tr>
                    <td class="label">Peso (kg):</td><td>{{ $record->weight ?? '-' }}</td>
                    <td class="label">Volume:</td><td>{{ $record->volume ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Dimensões:</td><td>{{ $record->dimension ?? '-' }}</td>
                    <td class="label">Desenho/Código:</td><td>{{ $record->drawing ?? '-' }}</td>
                </tr>
            </table>
        </div>
        
    </div>
</body>
</html>