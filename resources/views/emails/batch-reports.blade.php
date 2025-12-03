<x-mail::message>

    <div style="padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 14px; color: #555;">
        {!! \Illuminate\Support\Str::markdown($body) !!}
    </div>
</x-mail::message>