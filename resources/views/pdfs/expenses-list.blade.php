<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Relatório de Despesas</title>
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
        .summary-box { float: right; width: 40%; margin-top: 20px; border: 2px solid #000; padding: 10px; }
        .summary-box table { width: 100%; }
        .summary-box td { padding: 5px 0; }
        .summary-box .label { font-weight: bold; }
        .summary-box .value { text-align: right; font-size: 1.2em; color: #d9534f; }
    </style>
</head>
<body>
    <div class="footer">
        {{ $settings->company_name ?? 'E-Zandu' }} | Relatório Gerado em: {{ date('d/m/Y H:i') }}
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

        <h1>Relatório de Despesas Operacionais</h1>
        <p style="margin-bottom: 10px;">Período do Relatório: Baseado nos filtros aplicados ({{ $records->count() }} despesas).</p>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Método</th>
                    <th style="text-align: right;">Valor (Kz)</th>
                    <th>Registo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($record->expense_date)->format('d/m/Y') }}</td>
                        <td>{{ $record->type }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($record->description, 50) }}</td>
                        <td>{{ $record->payment_method }}</td>
                        <td style="text-align: right;">{{ number_format($record->amount, 2, ',', '.') }}</td>
                        <td>{{ $record->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhuma despesa encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="summary-box">
            <table>
                <tr>
                    <td class="label">Total de Despesas Filtradas:</td>
                    <td class="value">{{ number_format($records->count(), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Valor Total das Despesas:</td>
                    <td class="value">{{ number_format($totalExpenses, 2, ',', '.') }} Kz</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>