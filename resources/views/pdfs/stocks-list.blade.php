<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Lista de Armazéns</title>
    <style>
        @page { size: A4 portrait; margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 11px; line-height: 1.3; }
        .header-table { width: 100%; border-bottom: 1px solid #555; margin-bottom: 10px; }
        .header-table td { padding: 5px 0; vertical-align: top; }
        .company-details { text-align: right; font-size: 11px; }
        h1 { text-align: center; font-size: 18px; margin-top: 0; margin-bottom: 20px; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .report-table th, .report-table td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        .report-table th { background-color: #f0f0f0; font-weight: bold; }
        .footer { position: fixed; bottom: -40px; left: 0; right: 0; text-align: center; font-size: 9px; color: #888; }
    </style>
</head>
<body>
    <div class="footer">
        {{ $settings->company_name ?? 'E-Zandu' }} | Lista Gerada em: {{ date('d/m/Y H:i') }}
    </div>

    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 50%; padding-left: 0;">
                    <h2 style="margin:0; font-size: 14px;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                </td>
                <td class="company-details" style="width: 50%; padding-right: 0;">
                    <strong>{{ $settings->company_name ?? 'E-Zandu' }}</strong><br>
                    Telefone: {{ $settings->company_phone ?? '' }}
                </td>
            </tr>
        </table>

        <h1>Lista de Armazéns e Resumo de Estoque</h1>
        <p style="margin-bottom: 10px;">Total de Armazéns: {{ $records->count() }}</p>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Nome do Armazém</th>
                    <th>Padrão</th>
                    <th>Localização</th>
                    <th style="text-align: right;">Total de Itens</th>
                    <th>Última Atualização</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGlobalItems = 0; @endphp
                @forelse($records as $record)
                    @php 
                        $totalItems = $record->movements()->sum('quantity');
                        $totalGlobalItems += $totalItems;
                    @endphp
                    <tr>
                        <td>{{ $record->name }}</td>
                        <td>{{ $record->is_default ? 'Sim' : 'Não' }}</td>
                        <td>{{ $record->location ?? '-' }}</td>
                        <td style="text-align: right;">{{ number_format($totalItems, 0, ',', '.') }}</td>
                        <td>{{ $record->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center;">Nenhum armazém encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold; background-color: #e0e0e0;">Total Global de Itens em Estoque:</td>
                    <td style="text-align: right; font-weight: bold; background-color: #e0e0e0;">{{ number_format($totalGlobalItems, 0, ',', '.') }}</td>
                    <td style="background-color: #e0e0e0;"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</body>
</html>