<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Lista de Fornecedores</title>
    <style>
        @page { size: A4 portrait; margin: 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 11px; line-height: 1.3; }
        /* ... (Estilos de cabeçalho e rodapé adaptados de orders-list.blade.php) ... */
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
                    @if($settings && $settings->logo_dark_path)
                        <img src="{{ public_path($settings->logo_dark_path) }}" alt="Logo" style="max-width: 150px; max-height: 40px;">
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

        <h1>Lista de Fornecedores Registados</h1>
        <p style="margin-bottom: 10px;">Total de Fornecedores: {{ $records->count() }}</p>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Nome da Empresa</th>
                    <th>Pessoa de Contacto</th>
                    <th>Telefone</th>
                    <th>E-mail</th>
                    <th>NIF</th>
                    <th>Registo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->name }}</td>
                        <td>{{ $record->contact_name ?? '-' }}</td>
                        <td>{{ $record->phone ?? '-' }}</td>
                        <td>{{ $record->email ?? '-' }}</td>
                        <td>{{ $record->nif ?? '-' }}</td>
                        <td>{{ $record->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhum fornecedor encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>