<x-mail::layout>
    {{-- O Header já está no layout --}}

    {{-- Corpo do E-mail (Texto + Botão) --}}
    {{ $slot }}

    {{-- Link alternativo do botão (Subcopy) --}}
    @isset($subcopy)
        <x-slot:subcopy>
            {{ $subcopy }}
        </x-slot:subcopy>
    @endisset

    {{-- O Footer já está no layout --}}
</x-mail::layout>