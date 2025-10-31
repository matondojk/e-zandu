<x-mail::message>
# Relatório Semanal de Performance

Prezado(a) Administrador(a),

Aqui está o resumo automático gerado pela Inteligência Artificial.

<div style="background-color: #f4f4f4; padding: 15px; border-radius: 8px; margin-top: 20px;">
    {!! \Illuminate\Support\Str::markdown($analysisText) !!}
</div>

<x-mail::button :url="url('/admin')">
Aceder ao Painel de Análise
</x-mail::button>

Obrigado,
{{ config('app.name') }} - Automação IA
</x-mail::message>