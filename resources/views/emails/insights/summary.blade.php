<x-mail::message>
# {{ $message->getSubject() }}

Olá,

O relatório detalhado gerado pela Inteligência Artificial **encontra-se em anexo (PDF)**.

<div style="background-color: #f4f4f4; padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 14px; color: #555;">
    {{-- Renderiza o texto passado pelo comando --}}
    {!! \Illuminate\Support\Str::markdown($analysisText) !!}
</div>

<x-mail::button :url="url('/admin')">
Aceder ao Painel
</x-mail::button>

Obrigado,
{{ config('app.name') }} - Automação IA
</x-mail::message>