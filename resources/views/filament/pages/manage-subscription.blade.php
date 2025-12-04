<x-filament-panels::page>
    
    {{-- ÁREA DE ALERTA (Só aparece se houver aviso vindo do PHP) --}}
    @if($alertType)
        <div class="p-4 rounded-lg flex items-center gap-3 shadow-sm
            {{ $alertType === 'danger' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-orange-50 text-orange-700 border border-orange-200' }}">
            
            @if($alertType === 'danger')
                <x-heroicon-s-exclamation-circle class="w-6 h-6 shrink-0" />
            @else
                <x-heroicon-s-bell-alert class="w-6 h-6 shrink-0" />
            @endif

            <div>
                <span class="font-bold block text-lg">
                    {{ $alertType === 'danger' ? 'Assinatura Expirada' : 'Assinatura a Expirar' }}
                </span>
                <p>{{ $alertMessage }}</p>
            </div>
        </div>
    @endif

    {{-- CARDS DE INFORMAÇÃO --}}
    {{-- Adicionei 'mt-6' aqui para dar a margem no topo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 mt-6">
        
        {{-- CARD 1: STATUS --}}
        <div class="relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex items-center gap-4">
            
            {{-- Ícone Dinâmico --}}
            <div class="flex items-center justify-center w-12 h-12 rounded-lg 
                @if($subscription->status === 'active') bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-500
                @elseif($subscription->status === 'pending') bg-yellow-50 text-yellow-600 dark:bg-yellow-500/10 dark:text-yellow-500
                @else bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-500 @endif">
                
                @if($subscription->status === 'active') <x-heroicon-o-check-circle class="w-6 h-6" />
                @elseif($subscription->status === 'pending') <x-heroicon-o-clock class="w-6 h-6" />
                @else <x-heroicon-o-x-circle class="w-6 h-6" /> @endif
            </div>

            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status da Assinatura</p>
                <h3 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white capitalize">
                    @switch($subscription->status)
                        @case('active') Ativo @break
                        @case('pending') Pagamento Pendente @break
                        @case('paused') Pausado @break
                        @case('canceled') Cancelado @break
                        @default {{ $subscription->status }}
                    @endswitch
                </h3>
            </div>
            
            {{-- Indicador visual de "Pulso" se estiver ativo --}}
            @if($subscription->status === 'active')
                <div class="absolute top-4 right-4 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                </div>
            @endif
        </div>

        {{-- CARD 2: PLANO ATUAL --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-500">
                <x-heroicon-o-star class="w-6 h-6" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Plano Contratado</p>
                <h3 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    {{ $subscription->plan_name ?? 'Nenhum' }}
                </h3>
            </div>
        </div>

        {{-- CARD 3: EXPIRAÇÃO --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-4 flex items-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-500">
                <x-heroicon-o-calendar-days class="w-6 h-6" />
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Próxima Renovação</p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                        {{ $subscription->ends_at ? $subscription->ends_at->format('d/m/Y') : '-' }}
                    </h3>
                    @if($subscription->ends_at)
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full 
                            {{ $subscription->ends_at->isPast() ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                            {{ $subscription->ends_at->diffForHumans() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Linha divisória --}}
    <div class="border-t border-gray-200 dark:border-gray-700 my-8"></div>

    {{-- ESTA É A LINHA QUE FAZ A TABELA APARECER --}}
    {{ $this->table }}

</x-filament-panels::page>