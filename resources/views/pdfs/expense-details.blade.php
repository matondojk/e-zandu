<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detalhes da Despesa #{{ $record->id }}</title>
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
        .amount-box { text-align: center; background-color: #d9534f; color: white; padding: 15px; border-radius: 5px; margin-top: 20px; }
        .amount-box .label { font-size: 14px; margin-bottom: 5px; display: block; }
        .amount-box .value { font-size: 2.5rem; font-weight: bold; display: block; }
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
                    Despesa ID: <strong>#{{ $record->id }}</strong><br>
                    Data da Despesa: <strong>{{ \Carbon\Carbon::parse($record->expense_date)->format('d/m/Y') }}</strong><br>
                    Registo: {{ $record->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>

        <h1>Comprovativo de Despesa</h1>

        <div class="info-section">
            <h2>Detalhes da Despesa</h2>
            <table class="details-grid">
                <tr>
                    <td class="label">Descrição:</td>
                    <td><strong style="font-size: 1.1rem;">{{ $record->description }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Tipo de Despesa:</td>
                    <td>{{ $record->type ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Método de Pagamento:</td>
                    <td>{{ $record->payment_method ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Data de Pagamento:</td>
                    <td>{{ \Carbon\Carbon::parse($record->expense_date)->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
        
        <div class="info-section">
            <h2>Notas</h2>
            @if($record->notes)
                <p>{!! nl2br(e($record->notes)) !!}</p>
            @else
                <p>Nenhuma nota adicional registada.</p>
            @endif
        </div>

        <div class="amount-box">
            <span class="label">VALOR DA DESPESA</span>
            <span class="value">{{ number_format($record->amount, 2, ',', '.') }} Kz</span>
        </div>
    </div>
</body>
</html>