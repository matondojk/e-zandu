<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Detalhes do Fornecedor</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; line-height: 1.5; }
        .container { width: 100%; margin: 0 auto; }
        /* ... (Estilos de cabeçalho adaptados) ... */
        .header-table { width: 100%; border-bottom: 1px solid #eee; margin-bottom: 20px; }
        .header-table td { padding: 10px 0; vertical-align: top; }
        .company-details { text-align: right; }
        h1 { font-size: 24px; color: #000; margin-bottom: 5px; text-align: center; }
        .info-section { margin-bottom: 20px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .info-section h2 { font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 0; margin-bottom: 10px; }
        .details-grid { width: 100%; border-collapse: collapse; }
        .details-grid td { padding: 8px 0; border: none; }
        .details-grid .label { font-weight: bold; width: 35%; padding-right: 10px; vertical-align: top; }
        .address-box { padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; min-height: 100px; }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    @if($settings && $settings->logo_dark_path)
                        <img src="{{ public_path($settings->logo_dark_path) }}" alt="Logo" style="max-width: 150px; max-height: 80px;">
                    @else
                        <h2 style="margin:0; font-size: 16px;">{{ $settings->company_name ?? 'E-Zandu' }}</h2>
                    @endif
                </td>
                <td class="company-details">
                    Fornecedor: <strong>{{ $record->name }}</strong><br>
                    NIF: <strong>{{ $record->nif ?? 'N/A' }}</strong><br>
                    Registo: {{ $record->created_at->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>

        <h1>Detalhes do Fornecedor</h1>

        <div class="info-section">
            <h2>Informações de Contacto</h2>
            <table class="details-grid">
                <tr>
                    <td class="label">Nome da Empresa:</td>
                    <td><strong style="font-size: 1.1rem;">{{ $record->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">NIF/Nº Contribuinte:</td>
                    <td>{{ $record->nif ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Pessoa de Contacto:</td>
                    <td>{{ $record->contact_name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Telefone/WhatsApp:</td>
                    <td>{{ $record->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">E-mail:</td>
                    <td>{{ $record->email ?? '-' }}</td>
                </tr>
            </table>
        </div>
        
        <div class="info-section">
            <h2>Endereço</h2>
            @if($record->address)
                <div class="address-box">
                    {!! nl2br(e($record->address)) !!}
                </div>
            @else
                <p>Nenhum endereço completo registado.</p>
            @endif
        </div>
    </div>
</body>
</html>