<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Atualização de IA</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 13px; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { max-height: 50px; margin-bottom: 5px; }
        .store-name { font-size: 18px; font-weight: bold; color: #111827; }
        
        h1 { color: #dc2626; font-size: 18px; margin-top: 25px; border-bottom: 1px solid #dc2626; padding-bottom: 5px; }
        h2, h3 { color: #1f2937; font-size: 15px; margin-top: 15px; }
        
        .section { margin-bottom: 30px; }
        .content-text { line-height: 1.5; text-align: justify; }
        
        /* Estilos para Tabelas (Caso a IA gere tabelas no Markdown) */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>
    <div class="header">
        @if(!empty($setting->logo_dark_path))
            <img src="{{ $setting->logo_dark_path }}" class="logo" alt="Logo">
        @endif
        <div class="store-name">{{ $setting->store_name ?? config('app.name') }}</div>
        <div>Relatório de Inteligência Artificial • {{ date('d/m/Y H:i') }}</div>
    </div>

    <div class="section">
        <h1>1. Previsão de Vendas (Próximos 6 Meses)</h1>
        
        <div class="content-text">
            {{-- Renderiza a análise gerada pela IA (Markdown -> HTML) --}}
            {!! \Illuminate\Support\Str::markdown($forecast['analysis'] ?? 'Análise não disponível.') !!}
        </div>
    </div>

    <div class="section">
        <h1>2. Análise de Cross-Sell & Marketing</h1>
        
        <div class="content-text">
            {{-- Renderiza a análise completa de Cross-Sell da IA --}}
            {!! \Illuminate\Support\Str::markdown($crossSell) !!}
        </div>
    </div>

    <div class="footer">
        Relatório gerado automaticamente pelo sistema de IA da {{ $setting->store_name ?? 'Loja' }}.
    </div>
</body>
</html>