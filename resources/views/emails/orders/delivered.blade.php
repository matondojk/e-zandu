@component('mail::message')
# O seu Pedido foi Entregue

Olá {{ $order->user->name ?? 'Cliente' }},

Boas notícias! O seu **Pedido #{{ $order->id }}** foi marcado como entregue.
Esperamos que goste dos seus produtos!

Obrigado por confiar na {{ config('app.name') }}!

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent