<div class="bg-gray-50 min-h-screen py-12 flex flex-col items-center justify-center">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <div class="text-center mb-12">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-50 text-blue-600 rounded-[2rem] mb-6 shadow-sm border border-blue-100">
                <i class="ph-duotone ph-receipt text-4xl animate-pulse"></i>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3 tracking-tight">Pagamento Pendente</h1>
            <p class="text-gray-500 text-lg max-w-lg mx-auto leading-relaxed">
                O seu pedido <span class="font-bold text-gray-900">#{{ substr($cart->id, 0, 8) }}</span> foi registado com sucesso. <br>
                Para finalizar, realize o pagamento abaixo.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            
            <div class="lg:col-span-3 space-y-6">
                
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8">
                        <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                            <i class="ph-fill ph-wallet text-zandu-red"></i> Dados para Pagamento
                        </h2>

                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100 mb-8">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-2xl shadow-sm text-gray-800 border border-gray-100">
                                @if($cart->paymentMethod->type === 'bank_transfer') <i class="ph-bold ph-bank"></i>
                                @elseif($cart->paymentMethod->type === 'express') <i class="ph-bold ph-phone"></i>
                                @else <i class="ph-bold ph-credit-card"></i> @endif
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Método Selecionado</p>
                                <p class="font-bold text-gray-900 text-lg leading-none">{{ $cart->paymentMethod->name }}</p>
                            </div>
                        </div>

                        @if($cart->paymentMethod->type === 'bank_transfer')
                            <div class="relative overflow-hidden rounded-3xl bg-gray-900 text-white p-8 shadow-2xl transform transition hover:scale-[1.01] duration-500">
                                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
                                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-32 h-32 bg-zandu-red/20 rounded-full blur-2xl"></div>
                                <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20"></div>
                                
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-10">
                                        <i class="ph-fill ph-sim-card text-4xl text-white/80 rotate-90"></i>
                                        <i class="ph-fill ph-bank text-3xl opacity-50"></i>
                                    </div>

                                    <div class="space-y-6">
                                        <div>
                                            <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">IBAN</p>
                                            <p class="font-mono text-xl md:text-2xl font-bold tracking-wider text-white select-all cursor-pointer hover:text-zandu-red transition" title="Copiar">
                                                {{ $cart->paymentMethod->bank_iban }}
                                            </p>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Nº Conta</p>
                                                <p class="font-mono text-sm font-bold tracking-wider opacity-90 select-all">
                                                    {{ $cart->paymentMethod->bank_account_number }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-[10px] text-gray-400 uppercase tracking-widest mb-1">Titular</p>
                                                <p class="font-bold text-sm tracking-wide opacity-90 truncate max-w-[150px] ml-auto">
                                                    {{ $cart->paymentMethod->bank_account_holder }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($cart->paymentMethod->type === 'express')
                            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-8 text-center">
                                <i class="ph-duotone ph-device-mobile text-4xl text-orange-400 mb-2"></i>
                                <p class="text-sm text-orange-800 mb-1 font-bold uppercase tracking-wide">Número Express</p>
                                <p class="text-4xl font-mono font-bold text-orange-600 tracking-tight select-all">{{ $cart->paymentMethod->phone_number }}</p>
                            </div>
                        @endif

                        <div class="mt-8 p-4 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex gap-3">
                                <i class="ph-fill ph-info text-blue-500 text-xl mt-0.5"></i>
                                <p class="text-sm text-blue-800 leading-relaxed">
                                    <span class="font-bold">Instruções:</span> {{ $cart->paymentMethod->instructions }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-gray-900 to-gray-700"></div>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-2">Total a Transferir</p>
                    <p class="text-4xl font-extrabold text-gray-900 tracking-tight">
                        Kz {{ number_format($cart->total_price, 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 p-8 relative">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ph-bold ph-upload-simple"></i> Enviar Comprovativo
                    </h3>
                    
                    <form wire:submit.prevent="saveProof">
                        <div class="mb-6">
                            <label class="relative flex flex-col items-center justify-center w-full h-64 border-2 border-gray-200 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-blue-50/50 hover:border-blue-300 transition-all duration-300 group overflow-hidden">
                                
                                @if($proof)
                                    <div class="text-center z-10 animate-fade-in-up">
                                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3 shadow-sm">
                                            <i class="ph-fill ph-check text-3xl"></i>
                                        </div>
                                        <p class="font-bold text-green-700 text-sm">Ficheiro Carregado!</p>
                                        <p class="text-xs text-gray-500 mt-1 max-w-[180px] truncate bg-white px-2 py-1 rounded-md border border-gray-100 mx-auto shadow-sm">
                                            {{ $proof->getClientOriginalName() }}
                                        </p>
                                    </div>
                                @else
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10 group-hover:-translate-y-1 transition duration-300">
                                        <div class="w-14 h-14 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 group-hover:shadow-md transition duration-300 border border-gray-100">
                                            <i class="ph-duotone ph-cloud-arrow-up text-3xl text-gray-400 group-hover:text-blue-500 transition"></i>
                                        </div>
                                        <p class="mb-2 text-sm text-gray-500 text-center px-4">
                                            <span class="font-bold text-gray-900">Clique para selecionar</span> <br> ou arraste o ficheiro
                                        </p>
                                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide bg-gray-100 px-2 py-1 rounded-md">PDF, JPG, PNG (Max 5MB)</p>
                                    </div>
                                @endif
                                
                                <input type="file" wire:model="proof" class="hidden" accept=".jpg,.jpeg,.png,.pdf" />
                            </label>
                            
                            <div wire:loading wire:target="proof" class="w-full mt-4">
                                <div class="flex items-center justify-center gap-2 text-xs text-blue-600 font-bold bg-blue-50 py-2.5 rounded-xl animate-pulse">
                                    <i class="ph-bold ph-spinner animate-spin"></i> A processar imagem...
                                </div>
                            </div>

                            @error('proof') 
                                <div class="mt-3 flex items-center gap-2 text-xs text-red-500 font-bold bg-red-50 py-3 px-4 rounded-xl animate-shake">
                                    <i class="ph-bold ph-warning-circle text-lg"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" 
                            class="w-full bg-gray-900 text-white font-bold py-4 rounded-xl hover:bg-zandu-red transition shadow-lg hover:shadow-xl transform active:scale-95 flex justify-center items-center gap-3 disabled:opacity-70 disabled:cursor-not-allowed group" 
                            wire:loading.attr="disabled">
                            
                            <span wire:loading.remove class="flex items-center gap-2">
                                Confirmar Envio <i class="ph-bold ph-paper-plane-right group-hover:translate-x-1 transition-transform"></i>
                            </span>
                            
                            <span wire:loading class="flex items-center gap-2">
                                <i class="ph ph-spinner animate-spin text-xl"></i> Enviando...
                            </span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-2px); }
            75% { transform: translateX(2px); }
        }
        .animate-shake { animation: shake 0.3s ease-in-out; }
    </style>
</div>