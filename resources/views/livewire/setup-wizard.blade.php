{{-- 
  Design atualizado:
  1. Cor de destaque mudada para Laranja (orange-600)
  2. Largura aumentada para 'max-w-screen-xl'
  3. Padding dos inputs aumentado para 'p-3'
  4. Adicionados campos de upload de Logo e Ícone com preview
--}}
<div class="mx-auto w-full max-w-screen-xl p-4 sm:p-6 lg:p-8"> {{-- ✅ 2. LARGURA AUMENTADA --}}
    <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        
        {{-- Cabeçalho --}}
        <div class="px-8 py-6 border-b border-gray-200 dark:border-white/10">
            <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                Bem-vindo(a) ao E-Zando!
            </h1>
            <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
                Vamos configurar a sua loja. (Passo {{ $currentStep }} de 3)
            </p>
        </div>
        
        {{-- Stepper (Indicador de Passo) --}}
        <nav class="flex items-center justify-center p-6" aria-label="Progress">
            <ol role="list" class="flex items-center space-x-4">
                {{-- Passo 1 --}}
                <li>
                    @if ($currentStep > 1)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600"> {{-- ✅ 1. COR LARANJA --}}
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" /></svg>
                    </span>
                    @elseif ($currentStep == 1)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-orange-600"> {{-- ✅ 1. COR LARANJA --}}
                        <span class="text-orange-600">1</span> {{-- ✅ 1. COR LARANJA --}}
                    </span>
                    @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">1</span>
                    </span>
                    @endif
                </li>
                {{-- Linha --}}
                <li><div class="h-0.5 w-16 bg-gray-300 dark:bg-gray-700"></div></li>
                {{-- Passo 2 --}}
                <li>
                    @if ($currentStep > 2)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600"> {{-- ✅ 1. COR LARANJA --}}
                        <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd" /></svg>
                    </span>
                    @elseif ($currentStep == 2)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-orange-600"> {{-- ✅ 1. COR LARANJA --}}
                        <span class="text-orange-600">2</span> {{-- ✅ 1. COR LARANJA --}}
                    </span>
                    @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">2</span>
                    </span>
                    @endif
                </li>
                {{-- Linha --}}
                <li><div class="h-0.5 w-16 bg-gray-300 dark:bg-gray-700"></div></li>
                {{-- Passo 3 --}}
                <li>
                    @if ($currentStep == 3)
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-orange-600"> {{-- ✅ 1. COR LARANJA --}}
                        <span class="text-orange-600">3</span> {{-- ✅ 1. COR LARANJA --}}
                    </span>
                    @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">3</span>
                    </span>
                    @endif
                </li>
            </ol>
        </nav>

        {{-- Corpo do Formulário --}}
        <div class="p-8"> {{-- Padding aumentado --}}
            {{-- Erro Geral --}}
            @error('general')
                <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900 dark:text-red-300">
                    {{ $message }}
                </div>
            @enderror

            <form wire:submit.prevent="nextStep">
                
                {{-- PASSO 1: GERAL --}}
                <div @if($currentStep != 1) class="hidden" @endif>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Informações da Loja</h3>
                    
                    {{-- Grelha para layout --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Coluna 1: Campos de Texto --}}
                        <div class="space-y-5">
                            {{-- Nome da Loja --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="store_name">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Nome da Loja</span>
                                </label>
                                <input type="text" wire:model.defer="data.store_name" id="store_name" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.store_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            {{-- NIF --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="nif">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">NIF</span>
                                </label>
                                <input type="text" wire:model.defer="data.nif" id="nif" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.nif') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            {{-- Admin Phone --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="admin_phone">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">WhatsApp (Admin)</span>
                                </label>
                                <input type="text" wire:model.defer="data.admin_phone" id="admin_phone" placeholder="Ex: 2449xxxxxxxx" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.admin_phone') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            {{-- Support Email --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="support_email">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Email de Suporte (Notificações)</span>
                                </label>
                                <input type="email" wire:model.defer="data.support_email" id="support_email" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.support_email') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Coluna 2: Uploads --}}
                        <div class="space-y-5">
                            {{-- ✅ 4. CAMPO DE UPLOAD LOGO --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="logo_path">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Logotipo (Fundo Transparente)</span>
                                </label>
                                <input type="file" wire:model="data.logo_path" id="logo_path" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 p-3">
                                <div wire:loading wire:target="data.logo_path" class="text-sm text-gray-500 mt-2">A carregar...</div>
                                @if ($data['logo_path'])
                                    <div class="mt-2 p-2 border rounded-lg">
                                        <span class="text-sm text-gray-600">Preview:</span>
                                        <img src="{{ $data['logo_path']->temporaryUrl() }}" class="h-16 w-auto">
                                    </div>
                                @endif
                                @error('data.logo_path') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            
                            {{-- ✅ 4. CAMPO DE UPLOAD ÍCONE --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="icon_path">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Ícone / Favicon (.ico, .png)</span>
                                </label>
                                <input type="file" wire:model="data.icon_path" id="icon_path" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 p-3">
                                <div wire:loading wire:target="data.icon_path" class="text-sm text-gray-500 mt-2">A carregar...</div>
                                @if ($data['icon_path'])
                                    <div class="mt-2 p-2 border rounded-lg">
                                        <span class="text-sm text-gray-600">Preview:</span>
                                        <img src="{{ $data['icon_path']->temporaryUrl() }}" class="h-16 w-16">
                                    </div>
                                @endif
                                @error('data.icon_path') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PASSO 2: PAGAMENTOS E STOCK --}}
                <div @if($currentStep != 2) class="hidden" @endif>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Pagamentos e Gestão de Stock</h3>
                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- IBAN --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="iban">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">IBAN (Transferência)</span>
                                </label>
                                <input type="text" wire:model.defer="data.iban" id="iban" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.iban') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            {{-- Multicaixa --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="multicaixa_express">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Multicaixa Express (Nº)</span>
                                </label>
                                <input type="text" wire:model.defer="data.multicaixa_express" id="multicaixa_express" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.multicaixa_express') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <hr class="dark:border-gray-700">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Taxa Frete --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="shipping_rate">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Taxa de Frete (Kz)</span>
                                </label>
                                <input type="number" step="100" wire:model.defer="data.shipping_rate" id="shipping_rate" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.shipping_rate') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            {{-- Stock Mínimo --}}
                            <div>
                                <label class="inline-flex items-center gap-x-3" for="minimum_stock">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Nível Mínimo de Stock (Alerta)</span>
                                </label>
                                <input type="number" wire:model.defer="data.minimum_stock" id="minimum_stock" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                @error('data.minimum_stock') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                             {{-- Emails Alerta Stock --}}
                            <div class="md:col-span-1">
                                <label class="inline-flex items-center gap-x-3" for="stock_alert_emails">
                                    <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Emails para Alertas de Stock</span>
                                </label>
                                <input type="text" wire:model.defer="data.stock_alert_emails" id="stock_alert_emails" placeholder="gestor@exemplo.com" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separar múltiplos emails com ( ; ).</p>
                                @error('data.stock_alert_emails') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- PASSO 3: IA --}}
                <div @if($currentStep != 3) class="hidden" @endif>
                     <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Contexto para Inteligência Artificial</h3>
                     <div class="space-y-5">
                        {{-- Descrição Empresa --}}
                        <div>
                            <label class="inline-flex items-center gap-x-3" for="company_description">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Descrição da Empresa (para IA)</span>
                            </label>
                            <textarea wire:model.defer="data.company_description" id="company_description" rows="4" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3" placeholder="Somos uma loja de..."></textarea> {{-- ✅ 3. INPUT MAIOR --}}
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">O que é a empresa? Qual a vossa missão? A IA usará isto para "O que vocês fazem?".</p>
                            @error('data.company_description') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                        {{-- Tipos de Produto (Texto Simples) --}}
                        <div>
                            <label class="inline-flex items-center gap-x-3" for="product_types_text">
                                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">Tipos de Produto (para IA)</span>
                            </label>
                            <input type="text" wire:model.defer="data.product_types_text" id="product_types_text" placeholder="Roupas, Calçados, Eletrónicos" class="block w-full rounded-lg shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white p-3"> {{-- ✅ 3. INPUT MAIOR --}}
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Separar por vírgula ( , ).Isto ajudará a IA a focar-se nos seus produtos.</p>
                            @error('data.product_types_text') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                {{-- Botões de Navegação --}}
                <div class="border-t border-gray-200 dark:border-white/10 mt-8 pt-6"> {{-- Margem aumentada --}}
                    <div class="flex items-center justify-between">
                        {{-- Botão Anterior --}}
                        <div>
                            <button type="button" wire:click="previousStep" @if($currentStep == 1) disabled @endif
                                class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-950 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"> {{-- Padding aumentado --}}
                                Anterior
                            </button>
                        </div>
                        
                        {{-- Botão Próximo/Finalizar --}}
                        <div>
                            @if ($currentStep < 3)
                                {{-- Usar o 'submit' do form para validar antes de avançar --}}
                                <button type="submit" 
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-500"> {{-- ✅ 1. COR LARANJA e Padding aumentado --}}
                                    Próximo Passo
                                </button>
                            @else
                                <button type="button" wire:click="save" 
                                    class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-500"> {{-- ✅ 1. COR LARANJA e Padding aumentado --}}
                                    <div wire:loading wire:target="save" class="animate-spin h-4 w-4 border-t-2 border-r-2 border-white rounded-full"></div>
                                    <span wire:loading.remove wire:target="save">Finalizar Configuração</span>
                                    <span wire:loading wire:target="save">A Salvar...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
    </div>
</div>