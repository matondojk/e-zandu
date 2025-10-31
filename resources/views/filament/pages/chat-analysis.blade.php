<x-filament-panels::page>
    <div class="flex flex-col h-[75vh]">
        
        {{-- 1. Área de Mensagens (Sem alteração) --}}
        <div 
            id="chat-container"
            class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900 rounded-t-lg"
            x-data="{
                scrollToBottom() {
                    const container = document.getElementById('chat-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }
            }"
            x-init="scrollToBottom()"
            @chat-updated.window="setTimeout(() => scrollToBottom(), 100)"
        >
            {{-- (Todo o loop @foreach do chat permanece igual) --}}
            @foreach ($chatHistory as $message)
                @if ($message['role'] === 'user')
                    <div class="flex justify-end">
                        <div class="max-w-xl p-3 bg-primary-500 text-white rounded-lg shadow-md">
                            <p>{{ $message['analysis'] }}</p>
                        </div>
                    </div>
                @else
                    <div class="flex justify-start">
                        <div class="max-w-3xl p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                {!! \Illuminate\Support\Str::markdown($message['analysis']) !!}
                            </div>
                            @if (!empty($message['data']))
                                <div class="mt-4 border-t pt-2">
                                    <h4 class="font-semibold text-xs mb-2 text-gray-500 dark:text-gray-400">
                                        Dados Retornados
                                        @if($message['sql_query'])
                                            (SQL: `{{ \Illuminate\Support\Str::limit($message['sql_query'], 50) }}`)
                                        @endif
                                    </h4>
                                    <div class="max-h-60 overflow-y-auto">
                                        @include('reports.view-template', [
                                            'data' => $message['data'], 
                                            'columns' => $message['columns'], 
                                            'title' => null 
                                        ])
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($isLoading)
                <div class="flex justify-start">
                    <div class="max-w-xl p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
                        <x-filament::loading-indicator class="h-6 w-6" />
                    </div>
                </div>
            @endif
        </div>

        {{-- 2. Área de Input (Formulário) --}}
        <form 
            wire:submit.prevent="sendMessage"
            wire:submit="$dispatch('chat-updated')" 
            class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-lg"
        >
            <div class="flex items-center space-x-3">
                {{-- Input (Sem alteração) --}}
                <div class="flex-1">
                    {{ $this->form->getComponent('currentMessage') }}
                </div>
                
                {{-- 
                  #########################################################
                  # ✅ CORREÇÃO AQUI: Botão com Feedback de Loading
                  #########################################################
                --}}
                <x-filament::button
                    type="submit"
                    icon="heroicon-o-paper-airplane"
                    class="rotate-90"
                    
                    {{-- 1. Desativa o botão INSTANTANEAMENTE ao clicar em 'sendMessage' --}}
                    wire:loading.attr="disabled"
                    wire:target="sendMessage"
                    
                    {{-- 2. Mostra o spinner do Filament quando $isLoading for true --}}
                    :loading-indicator="$isLoading"
                >
                    {{-- 3. Texto que aparece QUANDO NÃO ESTÁ A CARREGAR --}}
                    <span wire:loading.remove wire:target="sendMessage">
                        Enviar
                    </span>
                    
                    {{-- 4. Texto que aparece QUANDO ESTÁ A CARREGAR --}}
                    <span wire:loading wire:target="sendMessage">
                        A Enviar...
                    </span>
                </x-filament::button>

            </div>
            
            {{-- (A linha de erro que apagámos continua apagada) --}}
            
        </form>
    </div>
</x-filament-panels::page>