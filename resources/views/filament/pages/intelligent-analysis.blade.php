<x-filament-panels::page>

    {{-- Formulário de Controlo --}}
    <form wire:submit="generate">
        {{ $this->form }}
        <div class="mt-6">

            {{--
              #########################################################
              # ✅ CORREÇÃO 1: Botão "Gerar Relatório" com Loading Correto
              #########################################################
            --}}
            <x-filament::button
                type="submit"
                icon="heroicon-o-check"
                wire:loading.attr="disabled"
                wire:target="generate"
                :loading-indicator="$isGenerating" {{-- Adicionado, pois o PHP já suporta --}}>
                <span wire:loading.remove wire:target="generate">Gerar Relatório</span>
                <span wire:loading wire:target="generate">A Gerar...</span>
            </x-filament::button>
        </div>
    </form>

    {{-- ÁREA DE RESULTADO --}}
    @if ($reportData || $mainView === 'forecast')
    <div class="mt-8 border-t-2 pt-6">

        @if ($mainView !== 'forecast' && $reportTitle)
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
            Resultado: {{ $reportTitle }}
        </h2>
        @endif

        {{-- Toolbar de Ações com ABAS (TABS) --}}
        <div class="flex flex-wrap items-center gap-3 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">

            {{-- 1. ABAS PRINCIPAIS --}}
            <span class="text-sm font-medium mr-2">Ver:</span>

            <x-filament::button
                size="sm"
                color="{{ $mainView === 'report' ? 'primary' : 'gray' }}"
                wire:click.prevent="setMainView('report')"
                icon="heroicon-o-presentation-chart-line"
                :disabled="!$reportData">
                Relatório / Gráfico
            </x-filament::button>

            <x-filament::button
                size="sm"
                color="{{ $mainView === 'analysis' ? 'primary' : 'gray' }}"
                wire:click.prevent="setMainView('analysis')"
                icon="heroicon-o-sparkles"
                :disabled="!$reportData">
                Análise IA
            </x-filament::button>

            {{--
                  #########################################################
                  # ✅ CORREÇÃO 2: Ícone correto para "Previsão"
                  #########################################################
                --}}
            <x-filament::button
                size="sm"
                color="{{ $mainView === 'forecast' ? 'primary' : 'gray' }}"
                wire:click.prevent="setMainView('forecast')"
                icon="heroicon-o-arrow-trending-up"> {{-- Estava 'chart-bar' --}}
                Previsão de Vendas
            </x-filament::button>

            {{-- Separador Visual --}}
            <span class="mx-4 border-l border-gray-300 dark:border-gray-700 h-6"></span>

            {{-- 2. ABAS SECUNDÁRIAS (Formatos de Relatório) --}}
            @if ($mainView === 'report')
            <span class="text-sm font-medium mr-2">Formato:</span>
            <x-filament::button size="sm" color="{{ $currentType === 'visualization' ? 'primary' : 'gray' }}"
                wire:click.prevent="setViewMode('report', 'visualization')" icon="heroicon-o-table-cells">
                Tabela
            </x-filament::button>
            <x-filament::button size="sm" color="{{ $currentType === 'bar' ? 'primary' : 'gray' }}"
                wire:click.prevent="setViewMode('chart', 'bar')" icon="heroicon-o-chart-bar"
                :disabled="$chartsDisabled" title="{{ $chartsDisabled ? 'Gráficos requerem 2+ colunas' : '' }}">
                Barras
            </x-filament::button>
            <x-filament::button size="sm" color="{{ $currentType === 'pie' ? 'primary' : 'gray' }}"
                wire:click.prevent="setViewMode('chart', 'pie')" icon="heroicon-o-chart-pie"
                :disabled="$chartsDisabled" title="{{ $chartsDisabled ? 'Gráficos requerem 2+ colunas' : '' }}">
                Pizza
            </x-filament::button>
            <x-filament::button size="sm" color="{{ $currentType === 'line' ? 'primary' : 'gray' }}"
                wire:click.prevent="setViewMode('chart', 'line')" icon="heroicon-o-presentation-chart-line"
                :disabled="$chartsDisabled" title="{{ $chartsDisabled ? 'Gráficos requerem 2+ colunas' : '' }}">
                Linha
            </x-filament::button>
            @endif

            {{-- 3. EXPORTS (Só aparecem na aba Relatório) --}}
            @if ($mainView === 'report')
            <span class="flex-grow"></span>
            <span class="text-sm font-medium mr-2">Exportar:</span>
            <x-filament::button size="sm" color="gray" wire:click.prevent="exportPdf"
                icon="heroicon-o-arrow-down-tray" wire:loading.attr="disabled" tag="button">
                PDF
            </x-filament::button>
            <x-filament::button size="sm" color="gray" wire:click.prevent="exportXlsx"
                icon="heroicon-o-document-arrow-down" wire:loading.attr="disabled" tag="button">
                Excel
            </x-filament::button>
            @endif
        </div>

        {{-- Área de Conteúdo --}}
        <div class="mt-6 p-4 bg-white dark:bg-gray-900 rounded-lg shadow min-h-[300px]">

            {{-- ABA 1: Relatório/Gráfico --}}
            @if ($mainView === 'report')
            @if ($currentMode === 'report' && $currentType === 'visualization')
            @include('reports.view-template', [
            'data' => $reportData,
            'columns' => $reportColumns,
            'title' => null
            ])

            @elseif ($currentMode === 'chart' && !$chartsDisabled)

            {{--
                          #########################################################
                          # ✅ CORREÇÃO 3 (PRINCIPAL): Usar as variáveis corretas
                          #########################################################
                        --}}
            <div
                style="width: 95%; max-width: 1000px; margin: 20px auto;"
                wire:key="{{ $currentMode }}-{{ $currentType }}-{{ $reportId }}"
                x-data="reportChart(
    @js($currentType),
    @js(array_column($reportData, $chartLabelColumn)),  {{-- <-- USAR A VARIÁVEL INTELIGENTE --}}
    @js(array_column($reportData, $chartDataColumn)),   {{-- <-- USAR A VARIÁVEL INTELIGENTE --}}
    @js($chartDataColumn ?? 'Valor')                     {{-- <-- USAR A VARIÁVEL INTELIGENTE --}}
    @js($chartDisplayLabel ?? 'Valor')
)">
                <canvas x-ref="canvas"></canvas>
            </div>
            @endif

            {{-- ABA 2: Análise da IA (Sem alteração) --}}
            @elseif ($mainView === 'analysis')
            @if ($analysisLoading)
            <div class="flex justify-center items-center p-8">
                <x-filament::loading-indicator class="h-8 w-8" />
                <span class="ml-4 text-lg">A IA está a analisar os dados...</span>
            </div>
            @elseif ($analysisResult)
            <div class="prose prose-sm dark:prose-invert max-w-none">
                {!! \Illuminate\Support\Str::markdown($analysisResult) !!}
            </div>
            @endif

            {{-- ABA 3: Previsão de Vendas (Sem alteração) --}}
            @elseif ($mainView === 'forecast')
            @if ($forecastLoading)
            <div class="flex justify-center items-center p-8">
                <x-filament::loading-indicator class="h-8 w-8" />
                <span class="ml-4 text-lg">A IA está a gerar a previsão...</span>
            </div>
            @elseif ($forecastData)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium mb-2">Previsão de Vendas (Próximos 6 Meses)</h3>
                    <div
                        style="width: 100%; margin: 10px auto;"
                        wire:key="forecast-chart"
                        x-data="forecastChart(@js($forecastData))">
                        <canvas x-ref="forecastCanvas"></canvas>
                    </div>
                </div>
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium mb-2">Análise da IA</h3>
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! \Illuminate\Support\Str::markdown($forecastAnalysis) !!}
                    </div>
                </div>
            </div>
            @endif

            @endif
        </div>
    </div>
    @endif

    {{-- Script Alpine.js (Completo e correto, sem alteração) --}}
    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            
            /**
             * Gráfico 1: reportChart (Lógica do eixo corrigida)
             */
            Alpine.data('reportChart', (type, labels, data, dataLabel) => ({
                init() {
                    if (typeof Chart === 'undefined') {
                        console.error('Chart.js não foi carregado.');
                        return;
                    }
                    const validTypes = ['bar', 'pie', 'line', 'radar'];
                    if (!type || !validTypes.includes(type)) {
                        console.error(`Tipo de gráfico inválido ou indefinido: '${type}'`);
                        return;
                    }
                    
                    const ctx = this.$refs.canvas.getContext('2d');
                    
                    let bgColors = [];
                    let borderColors = [];
                    if(type === 'pie' || type === 'radar') {
                        for(let i = 0; i < labels.length; i++) {
                            const r = Math.floor(Math.random() * 255);
                            const g = Math.floor(Math.random() * 255);
                            const b = Math.floor(Math.random() * 255);
                            bgColors.push(`rgba(${r}, ${g}, ${b}, 0.2)`);
                            borderColors.push(`rgba(${r}, ${g}, ${b}, 1)`);
                        }
                    } else {
                        bgColors = ['rgba(54, 162, 235, 0.2)'];
                        borderColors = ['rgba(54, 162, 235, 1)'];
                    }
                    
                    // --- ✅ CORREÇÃO: Define as opções de gráfico ---
                    let options = {
                        responsive: true,
                        plugins: {
                            legend: { position: (type === 'pie') ? 'top' : 'bottom' }
                        },
                        scales: {
                            y: { beginAtZero: true, display: (type !== 'pie' && type !== 'radar') },
                            x: { display: (type !== 'pie' && type !== 'radar') }
                        }
                    };

                    // Torna o gráfico de barras HORIZONTAL e inverte a ordem
                    if (type === 'bar') {
                        options.indexAxis = 'y'; // Faz com que as barras sejam horizontais
                        options.scales = {
                            x: { // Eixo X (o das vendas)
                                beginAtZero: true, 
                                display: true
                            },
                            y: { // Eixo Y (o dos nomes)
                                display: true,
                                reverse: false // Inverte para o maior valor ficar no topo
                            }
                        };
                    }
                    // --- FIM DA CORREÇÃO ---

                    new Chart(ctx, {
                        type: type, 
                        data: {
                            labels: labels,
                            datasets: [{
                                label: dataLabel,
                                data: data,
                                backgroundColor: bgColors,
                                borderColor: borderColors,
                                borderWidth: 1,
                                fill: (type === 'radar' || type === 'line') ? true : false,
                            }]
                        },
                        options: options // Usa o novo objeto options
                    });
                }
            }));

            /**
             * Gráfico 2: forecastChart (Sem alteração, pois já é linha vertical)
             */
            Alpine.data('forecastChart', (chartData) => ({
                init() {
                    if (typeof Chart === 'undefined') { return; }
                    if (!chartData || !chartData.historical || !chartData.forecast) { return; }
                    const ctx = this.$refs.forecastCanvas.getContext('2d');
                    const historical = chartData.historical;
                    const forecast = chartData.forecast;
                    const allLabels = [
                        ...historical.map(d => d.mes),
                        ...forecast.map(d => d.mes)
                    ];
                    const historicalData = historical.map(d => d.total_vendas);
                    const forecastData = [
                        ...Array(historical.length).fill(null),
                        ...forecast.map(d => d.total_vendas)
                    ];
                    new Chart(ctx, {
                        type: 'line', 
                        data: {
                            labels: allLabels,
                            datasets: [
                                {
                                    label: 'Vendas Históricas',
                                    data: historicalData,
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    fill: true,
                                    tension: 0.1
                                },
                                {
                                    label: 'Previsão de Vendas',
                                    data: forecastData,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    borderDash: [5, 5], 
                                    fill: true,
                                    tension: 0.1
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' }
                            },
                            scales: {
                                y: { beginAtZero: true },
                            }
                        }
                    });
                }
            }));
        });
    </script>
    @endpush

</x-filament-panels::page>