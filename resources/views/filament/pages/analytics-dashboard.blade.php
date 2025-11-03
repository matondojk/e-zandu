<x-filament-panels::page>
    {{-- Renderiza o formulário de filtro no topo --}}
    {{ $this->form }}
    
    <div class="space-y-6">
        {{-- 1. CARDS DE ESTATÍSTICAS (4 Cards - Passamos o estado) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Usamos Livewire Component para passar o estado --}}
            @livewire('sales-stats-overview-widget', [
                'startDate' => $this->data['start_date'],
                'endDate' => $this->data['end_date']
            ])
        </div>

        {{-- 2. GRÁFICO DE VENDAS (Filament Widget) --}}
        <div class="fi-page-widget-column-container space-y-6">
            @livewire('sales-chart-widget', [
                'startDate' => $this->data['start_date'],
                'endDate' => $this->data['end_date']
            ])
        </div>
        
        {{-- 3. TABELAS DE DADOS (Últimos Pedidos e Estoque Baixo) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                @livewire('latest-orders-table-widget', [
                    'startDate' => $this->data['start_date'],
                    'endDate' => $this->data['end_date']
                ])
            </div>
            {{-- Estoque não precisa de filtro de data --}}
            <div>
                @livewire('low-stock-table-widget')
            </div>
        </div>
        
        {{-- 4. GRÁFICO DE COMPRAS --}}
        <div class="fi-page-widget-column-container space-y-6">
            @livewire('purchases-chart-widget', [
                'startDate' => $this->data['start_date'],
                'endDate' => $this->data['end_date']
            ])
        </div>
    </div>
</x-filament-panels::page>