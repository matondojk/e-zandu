<div class="bg-gray-50 min-h-screen font-sans">
    
    <div class="relative bg-gray-900 py-20 md:py-24 border-b border-gray-800 overflow-hidden">
        <div class="absolute inset-0 opacity-20 mix-blend-overlay">
            <img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?q=80&w=2070&auto=format&fit=crop" alt="Background City" class="w-full h-full object-cover">
        </div>
        
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-zandu-red/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative container mx-auto px-4 text-center md:text-left z-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gray-700 bg-gray-800/50 backdrop-blur-sm mb-6">
                        <span class="w-2 h-2 bg-zandu-red rounded-full animate-pulse"></span>
                        <span class="text-gray-300 font-bold tracking-wider text-xs uppercase">Suporte 24/7</span>
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 tracking-tight">
                        Fale Connosco
                    </h1>
                    
                    <p class="text-gray-400 text-lg max-w-xl leading-relaxed font-light">
                        Estamos aqui para ajudar. Dúvidas sobre produtos, pedidos ou parcerias? Entre em contacto com a nossa equipa.
                    </p>
                </div>
                
                <div class="hidden md:block opacity-10 transform rotate-12">
                    <i class="ph-fill ph-chat-circle-dots text-9xl text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto pt-20 px-4 py-12 -mt-10 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-xl">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <i class="ph-fill ph-info text-zandu-red"></i> Canais de Atendimento
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="group flex gap-4 items-start p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition shadow-sm">
                                <i class="ph-bold ph-map-pin text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm mb-1">Sede Zandu</h3>
                                <p class="text-gray-500 text-sm leading-relaxed">{{ $settings->company_address ?? 'Luanda, Angola' }}</p>
                            </div>
                        </div>

                        <div class="group flex gap-4 items-start p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-green-50 text-green-600 flex items-center justify-center flex-shrink-0 group-hover:bg-green-600 group-hover:text-white transition shadow-sm">
                                <i class="ph-bold ph-phone text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm mb-1">Telefone</h3>
                                <a href="tel:{{ $settings->support_phone ?? '' }}" class="text-gray-500 text-sm hover:text-zandu-red transition font-medium">
                                    {{ $settings->support_phone ?? '+244 9XX XXX XXX' }}
                                </a>
                                <p class="text-xs text-gray-400 mt-1">Seg-Sex: 8h às 18h</p>
                            </div>
                        </div>

                        <div class="group flex gap-4 items-start p-4 rounded-2xl hover:bg-gray-50 transition border border-transparent hover:border-gray-100">
                            <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center flex-shrink-0 group-hover:bg-orange-600 group-hover:text-white transition shadow-sm">
                                <i class="ph-bold ph-envelope-simple text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 text-sm mb-1">Email</h3>
                                <a href="mailto:{{ $settings->support_email ?? '' }}" class="text-gray-500 text-sm hover:text-zandu-red transition font-medium break-all">
                                    {{ $settings->support_email ?? 'suporte@zandu.ao' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Redes Sociais</h3>
                        <div class="flex gap-3">
                            @if($settings->facebook_url)
                                <a href="{{ $settings->facebook_url }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-[#1877F2] hover:text-white transition transform hover:scale-110">
                                    <i class="ph-fill ph-facebook-logo text-xl"></i>
                                </a>
                            @endif
                            @if($settings->instagram_url)
                                <a href="{{ $settings->instagram_url }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-[#E4405F] hover:text-white transition transform hover:scale-110">
                                    <i class="ph-fill ph-instagram-logo text-xl"></i>
                                </a>
                            @endif
                            @if($settings->whatsapp_url)
                                <a href="{{ $settings->whatsapp_url }}" target="_blank" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-[#25D366] hover:text-white transition transform hover:scale-110">
                                    <i class="ph-fill ph-whatsapp-logo text-xl"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="bg-white rounded-3xl shadow-2xl border border-gray-100 p-8 md:p-10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-gray-50 to-transparent rounded-bl-full -mr-10 -mt-10"></div>

                    <div class="relative z-10">
                        <div class="mb-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Envie uma Mensagem</h2>
                            <p class="text-gray-500">Preencha o formulário abaixo. Respondemos geralmente em menos de 2 horas.</p>
                        </div>

                        @if($successMessage)
                            <div class="bg-green-50 border border-green-200 text-green-700 px-6 py-4 rounded-2xl mb-8 flex items-center gap-3 shadow-sm animate-pulse-slow">
                                <i class="ph-fill ph-check-circle text-2xl"></i>
                                <div>
                                    <p class="font-bold">Sucesso!</p>
                                    <p class="text-sm">{{ $successMessage }}</p>
                                </div>
                            </div>
                        @endif

                        <form wire:submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nome Completo</label>
                                    <div class="relative group">
                                        <i class="ph-bold ph-user absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition"></i>
                                        <input type="text" wire:model="name" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="Seu nome">
                                    </div>
                                    @error('name') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Telefone</label>
                                    <div class="relative group">
                                        <i class="ph-bold ph-phone absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition"></i>
                                        <input type="text" wire:model="phone" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="9XX XXX XXX">
                                    </div>
                                    @error('phone') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Email</label>
                                    <div class="relative group">
                                        <i class="ph-bold ph-envelope absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition"></i>
                                        <input type="email" wire:model="email" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="email@exemplo.com">
                                    </div>
                                    @error('email') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Assunto</label>
                                    <div class="relative group">
                                        <i class="ph-bold ph-tag absolute left-4 top-3.5 text-gray-400 group-focus-within:text-zandu-red transition"></i>
                                        <input type="text" wire:model="subject" class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-11 pr-4 py-3 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition placeholder-gray-400" placeholder="Motivo do contacto">
                                    </div>
                                    @error('subject') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Mensagem</label>
                                <div class="relative group">
                                    <textarea wire:model="message" rows="5" class="w-full bg-gray-50 border border-gray-200 rounded-xl p-4 text-sm focus:bg-white focus:border-zandu-red focus:ring-1 focus:ring-zandu-red outline-none transition resize-none placeholder-gray-400" placeholder="Como podemos ajudar?"></textarea>
                                </div>
                                @error('message') <span class="text-red-500 text-xs mt-1 font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" wire:loading.attr="disabled" class="w-full md:w-auto bg-gray-900 hover:bg-zandu-red text-white font-bold py-4 px-10 rounded-xl transition shadow-lg hover:shadow-xl flex items-center justify-center gap-2 disabled:opacity-70 transform active:scale-95">
                                    <span wire:loading.remove>Enviar Mensagem</span>
                                    <span wire:loading><i class="ph ph-spinner animate-spin"></i> A enviar...</span>
                                    <i class="ph-bold ph-paper-plane-right" wire:loading.remove></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 mb-12">
        <div class="w-full h-[400px] bg-gray-200 rounded-3xl overflow-hidden shadow-lg border border-gray-200 relative group">
            <iframe 
                src="https://maps.google.com/maps?q={{ urlencode($settings->company_address ?? 'Luanda, Angola') }}&t=&z=13&ie=UTF8&iwloc=&output=embed" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade"
                class="grayscale group-hover:grayscale-0 transition-all duration-700"
            ></iframe>
            
            <div class="absolute bottom-6 left-6 bg-white px-6 py-3 rounded-xl shadow-xl text-sm font-bold text-gray-800 pointer-events-none flex items-center gap-2 animate-bounce">
                <i class="ph-fill ph-map-pin text-zandu-red text-lg"></i> 
                <span>Localização Zandu</span>
            </div>
        </div>
    </div>
</div>