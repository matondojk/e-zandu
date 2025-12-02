<div class="bg-gray-50 min-h-screen py-12 font-sans">
    <div class="container mx-auto px-4">
        
        <div class="lg:hidden mb-8 flex items-center gap-3">
            <div class="w-10 h-10 bg-zandu-red rounded-xl flex items-center justify-center text-white">
                <i class="ph-bold ph-user"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Minha Conta</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <div class="lg:col-span-4 xl:col-span-3">
                <x-account-sidebar />
            </div>

            <div class="lg:col-span-8 xl:col-span-9 space-y-8">
                
                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden relative">
                    <div class="absolute top-8 right-8 hidden md:block z-10">
                        <button type="submit" form="profileForm" wire:loading.attr="disabled" class="bg-gray-900 text-white font-bold py-3 px-6 rounded-xl hover:bg-zandu-red transition shadow-lg flex items-center gap-2 disabled:opacity-70 transform hover:-translate-y-0.5">
                            <span wire:loading.remove target="updateProfile">Salvar Alterações</span>
                            <span wire:loading target="updateProfile"><i class="ph ph-spinner animate-spin"></i> Salvando...</span>
                        </button>
                    </div>

                    <div class="p-8 md:p-10">
                        <div class="mb-10">
                            <h2 class="text-2xl font-bold text-gray-900">Dados Pessoais</h2>
                            <p class="text-gray-500 text-sm mt-1">Gerencie sua foto de perfil e informações de contato.</p>
                        </div>

                        <form id="profileForm" wire:submit.prevent="updateProfile">
                            
                            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 mb-10 p-6 bg-gray-50 rounded-3xl border border-gray-100 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-bl-full opacity-50 pointer-events-none"></div>

                                <div class="relative group">
                                    <div class="w-28 h-28 rounded-full bg-white p-1 shadow-md ring-4 ring-white">
                                        <div class="w-full h-full rounded-full overflow-hidden relative bg-gray-100">
                                            @if ($avatar)
                                                <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif ($existingAvatar)
                                                 <img src="{{ filter_var($existingAvatar, FILTER_VALIDATE_URL) ? $existingAvatar : Storage::disk('public')->url($existingAvatar) }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                                    <i class="ph-fill ph-user text-5xl"></i>
                                                </div>
                                            @endif
                                            
                                            <div wire:loading wire:target="avatar" class="absolute inset-0 bg-black/50 flex items-center justify-center backdrop-blur-sm">
                                                <i class="ph-bold ph-spinner text-white animate-spin text-2xl"></i>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <label for="avatar-upload" class="absolute bottom-1 right-1 w-9 h-9 bg-gray-900 text-white rounded-full flex items-center justify-center cursor-pointer hover:bg-zandu-red transition shadow-lg border-2 border-white group-hover:scale-110" title="Alterar Foto">
                                        <i class="ph-bold ph-camera"></i>
                                    </label>
                                    <input type="file" id="avatar-upload" wire:model="avatar" class="hidden" accept="image/*">
                                </div>
                                
                                <div class="text-center md:text-left flex-1 relative z-10">
                                    <h3 class="font-bold text-gray-900 text-lg mb-1">Sua Foto</h3>
                                    <p class="text-sm text-gray-500 mb-4 max-w-xs mx-auto md:mx-0">Carregue uma nova imagem para personalizar o seu perfil. Recomendamos 500x500px.</p>
                                    <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-white border border-gray-200 px-2 py-1 rounded-md text-gray-500">JPG</span>
                                        <span class="text-[10px] font-bold uppercase tracking-wider bg-white border border-gray-200 px-2 py-1 rounded-md text-gray-500">PNG</span>
                                    </div>
                                    @error('avatar') <span class="text-red-500 text-xs font-bold mt-2 block bg-red-50 px-2 py-1 rounded-lg inline-block"><i class="ph-bold ph-warning"></i> {{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Nome Completo</label>
                                    <div class="relative group">
                                        <input type="text" wire:model="name" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition font-medium">
                                        <i class="ph-bold ph-user absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                    @error('name') <span class="text-red-500 text-xs font-bold ml-1">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Email</label>
                                    <div class="relative">
                                        <input type="email" wire:model="email" readonly class="w-full bg-gray-50 border border-gray-200 text-gray-500 cursor-not-allowed rounded-xl px-4 py-3.5 outline-none font-medium">
                                        <i class="ph-bold ph-lock absolute right-4 top-3.5 text-gray-400" title="Não editável"></i>
                                    </div>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Telefone / WhatsApp</label>
                                    <div class="relative group">
                                        <input type="text" wire:model="phone" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition font-medium">
                                        <i class="ph-bold ph-phone absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                    @error('phone') <span class="text-red-500 text-xs font-bold ml-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2 space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Endereço de Entrega Padrão</label>
                                    <div class="relative group">
                                        <textarea wire:model="delivery_address" rows="3" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition font-medium resize-none" placeholder="Rua, Número, Bairro, Ponto de referência..."></textarea>
                                        <i class="ph-bold ph-map-pin absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                    @error('delivery_address') <span class="text-red-500 text-xs font-bold ml-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-gray-100 md:hidden">
                                <button type="submit" wire:loading.attr="disabled" class="w-full bg-gray-900 text-white font-bold py-3.5 rounded-xl hover:bg-zandu-red transition shadow-lg flex justify-center items-center gap-2 disabled:opacity-70">
                                    <span wire:loading.remove target="updateProfile">Salvar Alterações</span>
                                    <span wire:loading target="updateProfile"><i class="ph ph-spinner animate-spin"></i></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex items-center gap-5 mb-8 pb-8 border-b border-gray-50">
                            <div class="w-14 h-14 rounded-2xl bg-red-50 text-zandu-red flex items-center justify-center text-2xl shadow-sm">
                                <i class="ph-bold ph-shield-check"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Segurança</h3>
                                <p class="text-sm text-gray-500 mt-1">Atualize a sua senha de acesso para manter sua conta segura.</p>
                            </div>
                        </div>

                        <form wire:submit.prevent="updatePassword" class="max-w-3xl">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Senha Atual</label>
                                    <div class="relative group">
                                        <input type="password" wire:model="current_password" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition">
                                        <i class="ph-bold ph-key absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                    @error('current_password') <span class="text-red-500 text-xs font-bold ml-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Nova Senha</label>
                                    <div class="relative group">
                                        <input type="password" wire:model="new_password" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition">
                                        <i class="ph-bold ph-lock-key absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                    @error('new_password') <span class="text-red-500 text-xs font-bold ml-1 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-gray-700 uppercase tracking-wider ml-1">Confirmar</label>
                                    <div class="relative group">
                                        <input type="password" wire:model="new_password_confirmation" class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition">
                                        <i class="ph-bold ph-check-circle absolute right-4 top-3.5 text-gray-300 group-focus-within:text-zandu-red transition"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-8">
                                <button type="submit" wire:loading.attr="disabled" class="bg-white border-2 border-gray-200 text-gray-700 font-bold py-3 px-8 rounded-xl hover:border-zandu-red hover:text-zandu-red hover:bg-red-50 transition text-sm flex items-center gap-2 disabled:opacity-50 shadow-sm">
                                    <span wire:loading.remove target="updatePassword">Atualizar Senha</span>
                                    <span wire:loading target="updatePassword"><i class="ph ph-spinner animate-spin"></i> Processando...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>