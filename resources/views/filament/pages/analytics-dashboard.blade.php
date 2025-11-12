<x-filament-panels::page>

    {{-- 1. FILTRO DE DATAS --}}
    {{ $this->form }}

    {{-- 
      2. WIDGET DE STATS
      ❗️ PARÂMETROS REMOVIDOS
    --}}
    <div class="mt-6">
        @livewire(\App\Livewire\SalesStatsOverviewWidget::class, 
            key('stats-overview'))
    </div>

    {{-- 3. GRELHA (GRID) PRINCIPAL --}}
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-12">

        {{-- LINHA 1 ❗️ PARÂMETROS REMOVIDOS --}}
        
        <div class="lg:col-span-6">
            @livewire(\App\Filament\Widgets\SalesChartWidget::class, 
                key('sales-chart'))
        </div>

        <div class="lg:col-span-6">
            @livewire(\App\Filament\Widgets\PurchasesChartWidget::class, 
                key('purchases-chart'))
        </div>


        {{-- LINHA 2 ❗️ PARÂMETROS REMOVIDOS --}}

        <div class="lg:col-span-6">
            @livewire(\App\Filament\Widgets\PaymentMethodsChartWidget::class, 
                key('payment-methods-chart'))
        </div>

        <div class="lg:col-span-6">
            @livewire(\App\Filament\Widgets\DeliveryLocationsChartWidget::class, 
                key('delivery-locations-chart'))
        </div>


        {{-- LINHA 3 ❗️ PARÂMETROS REMOVIDOS --}}

        <div class="lg:col-span-6">
            @livewire(\App\Filament\Widgets\LatestOrdersTableWidget::class, 
                key('latest-orders'))
        </div>

        <div class="lg:col-span-6">
            {{-- Este widget não usa filtro, por isso já estava correto --}}
            @livewire(\App\Filament\Widgets\LowStockTableWidget::class, 
                key('low-stock'))
        </div>

    </div>

</x-filament-panels::page>