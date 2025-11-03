<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Configuração Inicial - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Carregar Tailwind via CDN (para corresponder ao Filament) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Estilos do Livewire -->
    @livewireStyles
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    
    <div class="min-h-screen flex flex-col items-center justify-center">
        <!-- O componente Livewire será injetado aqui -->
        {{ $slot }}
    </div>

    <!-- Scripts do Livewire -->
    @livewireScripts
</body>
</html>
