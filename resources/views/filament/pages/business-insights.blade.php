<x-filament-panels::page>
    <x-filament::card>
        {{-- Área de Controlo (Botão) --}}
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Clique no botão abaixo para que o Assistente de IA analise os principais KPIs (Indicadores Chave de Desempenho) do seu negócio.
                A IA irá verificar vendas recentes, produtos populares, clientes valiosos e riscos de stock.
            </p>

            <x-filament::button
                wire:click="runAnalysis"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                icon="heroicon-o-sparkles"
            >
                <span wire:loading.remove wire:target="runAnalysis">
                    Gerar Insights Agora
                </span>
                <span wire:loading wire:target="runAnalysis">
                    A IA está a analisar...
                </span>
            </x-filament::button>
        </div>

        {{-- Área de Resultados --}}
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">

            {{-- Estado de Carregamento (Loading) --}}
            <div wire:loading.flex wire:target="runAnalysis" class="items-center justify-center w-full h-48">
                <x-filament::loading-indicator class="h-8 w-8" />
                <span class="ml-4 text-lg">A analisar a saúde do negócio...</span>
            </div>

            {{-- Estado de Resultados (Após carregar) --}}
            <div wire:loading.remove wire:target="runAnalysis">
                @if ($insightsResult)
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! \Illuminate\Support\Str::markdown($insightsResult) !!}
                    </div>
                @else
                    {{-- Estado Inicial (Antes do primeiro clique) --}}
                    <div class="text-center text-gray-500">
                        <x-heroicon-o-beaker class="w-12 h-12 mx-auto mb-2" />
                        Os resultados da análise aparecerão aqui.
                    </div>
                @endif
            </div>
        </div>
    </x-filament::card>
</x-filament-panels::page>