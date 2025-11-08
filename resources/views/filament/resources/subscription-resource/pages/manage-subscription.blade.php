<x-filament-panels::page>

    {{-- 1. Resumo do Status (Stats) --}}
    @if($details)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="filament-card p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Status da Assinatura</h3>
                <p class="text-2xl font-bold dark:text-white mt-1">{{ ucfirst($details->status) }}</p>
            </div>
            <div class="filament-card p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Plano Atual</h3>
                <p class="text-2xl font-bold dark:text-white mt-1">{{ $details->plan_name }}</p>
            </div>
            <div class="filament-card p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Expira em</h3>
                <p class="text-2xl font-bold dark:text-white mt-1">
                    {{ $details->ends_at ? $details->ends_at->format('d/m/Y') : 'N/A' }}
                </p>
            </div>
        </div>
    @endif

    {{-- Container principal para Pagamento e Histórico --}}
    <div class="space-y-6 mt-6">
        
        {{-- 2. Bloco de Ações Pendentes --}}
        @if($details?->status === 'pending')
        
            {{-- Caixa de "Aguardando Ativação" --}}
            <div class="filament-card p-6 bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <div class="flex items-center gap-4">
                    <div>
                        <x-heroicon-o-clock class="h-8 w-8 text-yellow-500" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">Aguardando Ativação</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Se já efetuou o pagamento e enviou o comprovativo, a sua conta será ativada assim que o pagamento for validado pela nossa equipa.
                            <br>
                            Se tiver urgência ou algum problema, por favor contacte o nosso <a href="mailto:suporte@e-zandu.com" class="text-primary-600 font-medium">suporte</a>.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Caixa de Métodos de Pagamento (Se houver métodos) --}}
            @if(!empty($paymentMethods))
                <div class="filament-card bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                    <div class="p-4 sm:p-6">
                        <h2 class="text-xl font-semibold tracking-tight mb-4">Efetuar Pagamento</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Se ainda não pagou, efetue o pagamento usando um dos métodos abaixo e envie o comprovativo usando o botão no topo da página.
                        </p>
                        
                        @foreach($paymentMethods as $method)
                            @if($method['manual_proof'])
                                <div class="border rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50 dark:border-gray-700">
                                    <h3 class="font-semibold text-lg">{{ $method['name'] }}</h3>
                                    @if($method['details'])
                                        <ul class="mt-2 space-y-1 text-sm">
                                            @foreach($method['details'] as $key => $value)
                                                <li>
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ $key }}:</span>
                                                    <span class="text-gray-800 dark:text-gray-200">{{ $value }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        @endif

        {{-- 3. Histórico de Pagamentos/Eventos --}}
        <div class="filament-card bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="p-4 sm:p-6">
                <h2 class="text-xl font-semibold tracking-tight mb-4">Histórico de Eventos</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left filament-table">
                    <thead class="bg-gray-50 dark:bg-gray-500/10">
                        <tr>
                            <th class="filament-table-header-cell p-4">Data</th>
                            <th class="filament-table-header-cell p-4">Evento</th>
                            <th class="filament-table-header-cell p-4">Descrição</th>
                            <th class="filament-table-header-cell p-4 text-right">Valor (Kz)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($history as $item)
                            <tr class="filament-table-row">
                                <td class="filament-table-cell p-4">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                <td class="filament-table-cell p-4">{{ $item->event }}</td>
                                <td class="filament-table-cell p-4">{{ $item->description }}</td>
                                <td class="filament-table-cell p-4 text-right font-mono">
                                    {{ $item->price ? number_format($item->price, 2, ',', '.') : '---' }}
                                </td>
                            </tr>
                        @empty
                            <tr class="filament-table-row">
                                <td colspan="4" class="filament-table-cell p-4 text-center">Nenhum histórico encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>