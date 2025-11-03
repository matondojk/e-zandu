<div class="grid grid-cols-4 gap-6 w-full">
    @foreach ($stats as $stat)
        <div class="text-center">
            {{ $stat }}
        </div>
    @endforeach
</div>
