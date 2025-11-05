@component('mail::message')
# Problema no Pagamento 

Olá {{ $order->user->name ?? 'Cliente' }},

Houve um problema ao processar o pagamento para o seu **Pedido #{{ $order->id }}**. O comprovativo enviado foi rejeitado.

@component('mail::panel')
**Motivo da Rejeição:**<br>
{{ $reason }}
@endcomponent

Por favor, aceda ao link de pagamento que recebeu anteriormente e submeta um novo comprovativo de pagamento válido.

Se tiver alguma dúvida, por favor contacte o nosso suporte.

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent