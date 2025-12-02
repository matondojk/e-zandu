<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    
    <div class="w-full max-w-5xl bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row h-auto md:min-h-[650px]">
        
        <div class="hidden md:flex md:w-1/2 bg-gray-900 relative items-center justify-center text-center px-12 overflow-hidden">
            <div class="absolute inset-0 opacity-30 mix-blend-overlay">
                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=2015&auto=format&fit=crop" alt="Login Background" class="w-full h-full object-cover">
            </div>
            
            <div class="absolute top-[-20%] right-[-20%] w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[100px] pointer-events-none"></div>
            <div class="absolute bottom-[-20%] left-[-20%] w-[400px] h-[400px] bg-zandu-red/20 rounded-full blur-[100px] pointer-events-none"></div>
            
            <div class="relative z-10 text-white space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-white/10 bg-white/5 backdrop-blur-md">
                    <span class="w-2 h-2 bg-zandu-red rounded-full animate-pulse"></span>
                    <span class="text-gray-200 font-bold tracking-wider text-xs uppercase">Zandu Store</span>
                </div>

                <h2 class="text-4xl lg:text-5xl font-extrabold tracking-tight leading-tight">
                    A tecnologia que <br> move você.
                </h2>
                
                <p class="text-gray-400 text-lg leading-relaxed max-w-sm mx-auto font-light">
                    Acesse sua conta para acompanhar pedidos, gerir endereços e aproveitar ofertas exclusivas.
                </p>
                
                <div class="pt-8 flex justify-center gap-8 border-t border-white/10 max-w-xs mx-auto">
                    <div>
                        <span class="block text-2xl font-bold text-white">+50k</span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Produtos</span>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div>
                        <span class="block text-2xl font-bold text-white">24h</span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Entrega</span>
                    </div>
                    <div class="w-px bg-white/10"></div>
                    <div>
                        <span class="block text-2xl font-bold text-white">4.9</span>
                        <span class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Avaliação</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-8 md:p-12 bg-white flex flex-col justify-center relative">
            
            <a href="{{ route('home') }}" class="absolute top-6 right-6 w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-900 transition">
                <i class="ph-bold ph-x text-xl"></i>
            </a>

            <div class="max-w-sm mx-auto w-full">
                
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">
                        {{ $isRegister ? 'Criar Conta' : 'Bem-vindo(a)' }}
                    </h2>
                    <p class="text-gray-500 text-sm">
                        {{ $isRegister ? 'Preencha os dados abaixo para começar.' : 'Insira seus dados para aceder.' }}
                    </p>
                </div>

                @if(!$isRegister)
                    <div x-data x-animate:enter="fade-in" class="space-y-6">
                        <form wire:submit.prevent="login" class="space-y-5">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email ou Telefone</label>
                                <div class="relative group">
                                    <i class="ph-bold ph-user absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition text-lg"></i>
                                    <input type="text" wire:model="loginInput" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="ex: 923000000">
                                </div>
                                @error('loginInput') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="flex justify-between items-center mb-1.5">
                                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Senha</span>
                                    <a href="#" class="text-xs text-zandu-red font-bold hover:underline">Esqueceu a senha?</a>
                                </label>
                                <div class="relative group">
                                    <i class="ph-bold ph-lock-key absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition text-lg"></i>
                                    <input type="password" wire:model="password" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="••••••••">
                                </div>
                                @error('password') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                            </div>

                            <button type="submit" wire:loading.attr="disabled" class="w-full bg-gray-900 text-white font-bold py-4 rounded-xl hover:bg-zandu-red transition shadow-lg hover:shadow-xl transform active:scale-95 disabled:opacity-70 flex items-center justify-center gap-2 mt-2">
                                <span wire:loading.remove>Aceder Conta</span>
                                <span wire:loading><i class="ph ph-spinner animate-spin"></i> Validando...</span>
                                <i class="ph-bold ph-sign-in" wire:loading.remove></i>
                            </button>
                        </form>

                        <div class="text-center pt-2">
                            <p class="text-sm text-gray-500">
                                Não tem uma conta? 
                                <button type="button" wire:click="$set('isRegister', true)" class="font-bold text-zandu-red hover:underline ml-1">
                                    Crie agora
                                </button>
                            </p>
                        </div>
                    </div>
                
                @else
                    <div x-data x-animate:enter="fade-in" class="space-y-6">
                        <form wire:submit.prevent="register" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nome Completo</label>
                                <div class="relative group">
                                    <i class="ph-bold ph-user absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition text-lg"></i>
                                    <input type="text" wire:model="name" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="Seu nome">
                                </div>
                                @error('name') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email</label>
                                <div class="relative group">
                                    <i class="ph-bold ph-envelope absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition text-lg"></i>
                                    <input type="email" wire:model="regEmail" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="seu@email.com">
                                </div>
                                @error('regEmail') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">WhatsApp / Telefone</label>
                                <div class="relative group">
                                    <i class="ph-bold ph-whatsapp-logo absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition text-lg"></i>
                                    <input type="text" wire:model="regPhone" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="244 9XX XXX XXX">
                                </div>
                                @error('regPhone') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Senha</label>
                                    <input type="password" wire:model="regPassword" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="******">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Confirmar</label>
                                    <input type="password" wire:model="regPassword_confirmation" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="******">
                                </div>
                            </div>
                            @error('regPassword') <span class="text-red-500 text-xs mt-1 font-medium block">{{ $message }}</span> @enderror

                            <button type="submit" wire:loading.attr="disabled" class="w-full bg-zandu-red text-white font-bold py-4 rounded-xl hover:bg-red-700 transition shadow-lg hover:shadow-xl transform active:scale-95 disabled:opacity-70 flex items-center justify-center gap-2 mt-2">
                                <span wire:loading.remove>Criar Minha Conta</span>
                                <span wire:loading><i class="ph ph-spinner animate-spin"></i> Criando...</span>
                                <i class="ph-bold ph-rocket-launch" wire:loading.remove></i>
                            </button>
                            
                            <p class="text-[10px] text-gray-400 text-center mt-4">
                                Ao criar conta, você concorda com nossos Termos e Política de Privacidade.
                            </p>
                        </form>

                        <div class="text-center pt-1 border-t border-gray-100 mt-4">
                            <p class="text-sm text-gray-500 mt-4">
                                Já tem uma conta? 
                                <button type="button" wire:click="$set('isRegister', false)" class="font-bold text-zandu-red hover:underline ml-1">
                                    Entrar
                                </button>
                            </p>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>