<x-filament-panels::page>

    {{-- 1. FILTRO DE DATAS --}}
    {{-- Apenas mantém o form para renderizar o filtro no cabeçalho --}}
    {{ $this->form }}

    {{-- 2. STATS OVERVIEW (Ocupa a largura total) --}}
    {{-- NÃO PASSAR OS PARÂMETROS DE DATA AQUI --}}
    <div class="mt-6">
        @livewire(\App\Livewire\SalesStatsOverviewWidget::class, key('stats-overview'))
    </div>

    {{-- 3. GRELHA (GRID) PRINCIPAL --}}
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-12">

        {{-- LINHA 1: Vendas (6 colunas) + Compras (6 colunas) --}}
        
        <div class="lg:col-span-6">
            {{-- REMOVIDOS os parâmetros [startDate => ..., endDate => ...] --}}
            @livewire(\App\Filament\Widgets\SalesChartWidget::class, key('sales-chart'))
        </div>

        <div class="lg:col-span-6">
            {{-- REMOVIDOS os parâmetros [startDate => ..., endDate => ...] --}}
            @livewire(\App\Filament\Widgets\PurchasesChartWidget::class, key('purchases-chart'))
        </div>


        {{-- LINHA 2: Pagamentos (6 colunas) + Locais (6 colunas) --}}

        <div class="lg:col-span-6">
            {{-- REMOVIDOS os parâmetros [startDate => ..., endDate => ...] --}}
            @livewire(\App\Filament\Widgets\PaymentMethodsChartWidget::class, key('payment-methods-chart'))
        </div>

        <div class="lg:col-span-6">
            {{-- REMOVIDOS os parâmetros [startDate => ..., endDate => ...] --}}
            @livewire(\App\Filament\Widgets\DeliveryLocationsChartWidget::class, key('delivery-locations-chart'))
        </div>


        {{-- LINHA 3: Pedidos (6 colunas) + Stock (6 colunas) --}}

        <div class="lg:col-span-6">
            {{-- REMOVIDOS os parâmetros [startDate => ..., endDate => ...] --}}
            @livewire(\App\Filament\Widgets\LatestOrdersTableWidget::class, key('latest-orders'))
        </div>

        <div class="lg:col-span-6">
            {{-- Este widget não usa filtro de data --}}
            @livewire(\App\Filament\Widgets\LowStockTableWidget::class, 
                key('low-stock'))
        </div>

    </div>

</x-filament-panels::page>