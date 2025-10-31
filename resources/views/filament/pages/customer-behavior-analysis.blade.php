<x-filament-panels::page>
    <x-filament::card>
        {{-- Área de Controlo (Botão) --}}
        <div class="mb-6">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Clique no botão abaixo para a IA analisar os pedidos pagos dos últimos 90 dias.
                Ela irá identificar padrões de produtos que são frequentemente comprados juntos e sugerir estratégias de cross-sell e bundles.
            </p>

            <x-filament::button
                wire:click="runAnalysis"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50"
                icon="heroicon-o-sparkles"
            >
                <span wire:loading.remove wire:target="runAnalysis">
                    Analisar Padrões de Compra
                </span>
                <span wire:loading wire:target="runAnalysis">
                    A IA está a analisar...
                </span>
            </x-filament::button>
        </div>

        {{-- Área de Resultados --}}
        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6 min-h-[200px]">

            {{-- Estado de Carregamento (Loading) --}}
            <div wire:loading.flex wire:target="runAnalysis" class="items-center justify-center w-full h-48">
                <x-filament::loading-indicator class="h-8 w-8" />
                <span class="ml-4 text-lg">A analisar padrões...</span>
            </div>

            {{-- Estado de Resultados (Após carregar) --}}
            <div wire:loading.remove wire:target="runAnalysis">
                @if ($analysisResult)
                    <div class="prose prose-sm dark:prose-invert max-w-none">
                        {!! \Illuminate\Support\Str::markdown($analysisResult) !!}
                    </div>
                @else
                    {{-- Estado Inicial (Antes do primeiro clique) --}}
                    <div class="text-center text-gray-500">
                        <x-heroicon-o-link class="w-12 h-12 mx-auto mb-2" />
                        Os insights de cross-sell aparecerão aqui.
                    </div>
                @endif
            </div>
        </div>
    </x-filament::card>
</x-filament-panels::page>