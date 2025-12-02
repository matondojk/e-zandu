<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resumo Semanal de Performance</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .logo { max-height: 60px; margin-bottom: 10px; }
        .store-name { font-size: 20px; font-weight: bold; color: #111827; }
        .date { font-size: 12px; color: #666; margin-top: 5px; }
        .content { line-height: 1.6; font-size: 14px; text-align: justify; }
        h1, h2, h3 { color: #dc2626; /* Zandu Red */ }
        strong { color: #000; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #aaa; padding: 10px; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($setting->logo_dark_path))
            {{-- Tenta carregar a imagem via URL, certifique-se que allow_url_fopen está on no php.ini --}}
            <img src="{{ $setting->logo_dark_path }}" class="logo" alt="Logo">
        @endif
        <div class="store-name">{{ $setting->store_name ?? config('app.name') }}</div>
        <div class="date">Gerado em: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="content">
        {{-- Converte o Markdown da IA para HTML --}}
        {!! \Illuminate\Support\Str::markdown($analysisText) !!}
    </div>

    <div class="footer">
        Relatório gerado automaticamente por Inteligência Artificial - {{ $setting->store_name ?? config('app.name') }}
    </div>
</body>
</html>