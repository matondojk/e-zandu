<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Catálogo de Produtos</title>
    <style>
        @page { size: A4 landscape; margin: 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 10px; line-height: 1.3; }
        .container { width: 100%; }
        .header-table { width: 100%; border-bottom: 1px solid #555; margin-bottom: 10px; }
        .header-table td { padding: 5px 0; vertical-align: top; }
        .company-details { text-align: right; font-size: 11px; }
        .logo { max-width: 150px; max-height: 40px; }
        h1 { text-align: center; font-size: 18px; margin-top: 0; margin-bottom: 20px; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th, .report-table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .report-table th { background-color: #f0f0f0; font-weight: bold; }
        .report-table td.text-right, .report-table th.text-right { text-align: right; }
        .currency { font-family: 'Helvetica', 'Arial', sans-serif; }
        .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 9px; color: #888; }
        .product-image { max-width: 40px; height: auto; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="footer">
        {{ $settings->company_name ?? 'E-Zandu' }} | Catálogo Gerado em: {{ date('d/m/Y H:i') }}
    </div>

    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 50%; padding-left: 0;">
                    @if($settings && $settings->logo_dark_path)
                        <img src="{{ public_path($settings->logo_dark_path) }}" alt="Logo" class="logo">
                    @else
                        <h2 style="margin:0; font-size: 14px;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                    @endif
                </td>
                <td class="company-details" style="width: 50%; padding-right: 0;">
                    <strong>{{ $settings->company_name ?? 'E-Zandu' }}</strong><br>
                    NIF: {{ $settings->company_nif ?? 'N/A' }}<br>
                    Telefone: {{ $settings->company_phone ?? '' }}
                </td>
            </tr>
        </table>

        <h1>Catálogo de Produtos (Inventário)</h1>
        <p style="margin-bottom: 10px;">
            Total de Produtos: {{ $records->count() }} (Filtros Aplicados)
        </p>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 30px;">Imagem</th>
                    <th>Nome do Produto</th>
                    <th>SKU</th>
                    <th>Categorias</th>
                    <th>Marcas</th>
                    <th class="text-right">Estoque</th>
                    <th class="text-right">Preço Normal</th>
                    <th class="text-right">Preço Promo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>
                            @if($record->image_url)
                                <img src="{{ $record->image_url }}" alt="Img" class="product-image">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $record->name }}</td>
                        <td>{{ $record->sku ?? '-' }}</td>
                        <td>{{ $record->categories->pluck('name')->implode(', ') }}</td>
                        <td>{{ $record->brands->pluck('name')->implode(', ') }}</td>
                        
                        @php
                            // Nota: A query já deveria ter carregado 'movements' via getEloquentQuery no Resource.
                            $currentStock = $record->movements->sum('quantity');
                        @endphp
                        <td class="text-right" style="font-weight: bold; color: {{ $currentStock > 0 ? 'green' : 'red' }};">
                            {{ number_format($currentStock, 0, ',', '.') }} {{ $record->unit ?? '' }}
                        </td>
                        
                        <td class="text-right">{{ number_format($record->price, 2, ',', '.') }} <span class="currency">Kz</span></td>
                        <td class="text-right" style="color: {{ $record->promotional_price > 0 ? 'green' : 'gray' }};">
                            @if($record->promotional_price > 0)
                                {{ number_format($record->promotional_price, 2, ',', '.') }} <span class="currency">Kz</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center;">Nenhum produto encontrado para os filtros aplicados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>