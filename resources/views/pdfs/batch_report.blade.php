<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportName }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 15px;
        }
        .logo {
            max-height: 60px;
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .meta-info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #dc2626;
            color: #ffffff;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }
        td {
            border-bottom: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .empty-data {
            text-align: center;
            padding: 50px;
            color: #888;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($setting) && !empty($setting->logo_dark_path))
            @php
                $path = public_path($setting->logo_dark_path);
                $logoSrc = $setting->logo_dark_path; 
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $dataImg = file_get_contents($path);
                    $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                }
            @endphp
            <img src="{{ $logoSrc }}" class="logo" alt="Logo">
        @endif
        
        <div class="report-title">{{ $reportName }}</div>
        <div class="meta-info">Gerado em: {{ $generatedAt }}</div>
    </div>

    {{-- Verificação segura para evitar erro de count() em string --}}
    @if(isset($data) && is_array($data) && count($data) > 0)
        <table>
            <thead>
                <tr>
                    @if(isset($columns) && is_array($columns))
                        @foreach($columns as $col)
                            <th>{{ ucfirst(str_replace('_', ' ', $col)) }}</th>
                        @endforeach
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($columns as $col)
                            <td>
                                @if(is_array($row))
                                    {{ $row[$col] ?? '-' }}
                                @elseif(is_object($row))
                                    {{ $row->$col ?? '-' }}
                                @else
                                    -
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-data">
            Não foram encontrados dados para este período.
        </div>
    @endif

    <div class="footer">
        Relatório Automático - {{ $setting->store_name ?? 'Sistema' }}
    </div>
</body>
</html>