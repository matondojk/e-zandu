<x-mail::message>
{{-- Removido: # {{ $subject }} - O assunto já é definido na Envelope do Mailable --}}

{!! $body !!} {{-- CORRIGIDO: Permite HTML não escapado --}}

Obrigado,
Equipa E-Zandu
</x-mail::message>