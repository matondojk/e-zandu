@component('mail::message')
# Pagamento Confirmado! ✅

Olá {{ $order->user->name ?? 'Cliente' }},

O seu pagamento para o **Pedido #{{ $order->id }}** foi confirmado com sucesso.
A sua encomenda já está a ser preparada para envio.

Enviámos a sua fatura em anexo neste email.

Obrigado por comprar connosco!

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent