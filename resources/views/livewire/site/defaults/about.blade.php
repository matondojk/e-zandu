<div class="bg-gray-50 min-h-screen font-sans">
    
    <div class="relative bg-gray-900 py-20 md:py-28 border-b border-gray-800 overflow-hidden">
        <div class="absolute inset-0 opacity-20 mix-blend-overlay">
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2069&auto=format&fit=crop" alt="Background Office" class="w-full h-full object-cover">
        </div>
        
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-zandu-red/10 rounded-full blur-[100px] pointer-events-none"></div>

        <div class="relative container mx-auto px-4 text-center md:text-left z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-gray-700 bg-gray-800/50 backdrop-blur-sm mb-6">
                <span class="w-2 h-2 bg-zandu-red rounded-full animate-pulse"></span>
                <span class="text-gray-300 font-bold tracking-wider text-xs uppercase">Nossa História</span>
            </div>
            
            <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-6 tracking-tight leading-tight">
                Apaixonados por <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">Tecnologia & Inovação.</span>
            </h1>
            
            <p class="text-gray-400 text-lg md:text-xl max-w-2xl leading-relaxed font-light">
                Mais do que vender produtos, conectamos você às melhores marcas globais com garantia, segurança e rapidez em Angola.
            </p>
        </div>
    </div>

    <div class="container mx-auto px-4 py-16">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-20 mb-24 items-center">
            <div class="lg:col-span-6 space-y-8">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">
                        A Sua Loja de Confiança <br> em <span class="relative inline-block">
                            <span class="relative z-10 text-zandu-red">Luanda e Angola.</span>
                            <span class="absolute bottom-1 left-0 w-full h-3 bg-red-100 -z-0 transform -rotate-2"></span>
                        </span>
                    </h2>
                    <div class="prose prose-lg text-gray-600 space-y-6 text-justify">
                        <p>
                            Fundada com o objetivo de simplificar o acesso à tecnologia de ponta, a <strong>{{ $settings->store_name ?? 'E-Zandu' }}</strong> estabeleceu-se como referência no mercado de eletrónicos. Trabalhamos diretamente com distribuidores oficiais para garantir que cada smartphone, computador ou acessório que chega às suas mãos seja 100% original.
                        </p>
                        <p>
                            Acreditamos que a experiência de compra deve ser tão premium quanto os produtos que vendemos. Por isso, investimos numa logística eficiente que nos permite entregar em tempo recorde, seja no centro de Luanda ou nas províncias.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-6 pt-6 border-t border-gray-100">
                    <div class="text-center md:text-left">
                        <span class="block text-3xl font-extrabold text-gray-900 mb-1">100%</span>
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wide">Originais</span>
                    </div>
                    <div class="text-center md:text-left">
                        <span class="block text-3xl font-extrabold text-gray-900 mb-1">+24h</span>
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wide">Suporte</span>
                    </div>
                    <div class="text-center md:text-left">
                        <span class="block text-3xl font-extrabold text-gray-900 mb-1">48h</span>
                        <span class="text-xs text-gray-500 uppercase font-bold tracking-wide">Entrega Média</span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6 relative">
                <div class="absolute top-10 -right-10 w-full h-full bg-gray-100 rounded-[3rem] -z-10 hidden lg:block"></div>
                <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl group">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent z-10 opacity-80"></div>
                    <img src="https://images.unsplash.com/photo-1531297420497-0b86953ec822?q=80&w=1200&auto=format&fit=crop" alt="Tech Store" class="w-full h-[500px] object-cover transform group-hover:scale-105 transition duration-700">
                    
                    <div class="absolute bottom-8 left-8 z-20 text-white max-w-xs">
                        <div class="w-12 h-12 bg-zandu-red rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                            <i class="ph-fill ph-medal text-2xl"></i>
                        </div>
                        <p class="font-bold text-xl mb-1">Qualidade Garantida</p>
                        <p class="text-sm text-gray-300 font-medium">Revendedor autorizado das principais marcas globais.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-24">
            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-red-50 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
                
                <div class="w-14 h-14 bg-red-50 text-zandu-red rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10 group-hover:bg-zandu-red group-hover:text-white transition">
                    <i class="ph-fill ph-seal-check"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3 relative z-10">Garantia Oficial</h3>
                <p class="text-gray-500 leading-relaxed text-sm relative z-10">
                    Todos os equipamentos comercializados possuem garantia de marca. Compre com a segurança de ter assistência técnica especializada.
                </p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
                
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10 group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="ph-fill ph-truck"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3 relative z-10">Envio Expresso</h3>
                <p class="text-gray-500 leading-relaxed text-sm relative z-10">
                    Não espere semanas. O nosso sistema logístico processa a sua encomenda imediatamente após o pagamento para envio rápido.
                </p>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition duration-300 group relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-green-50 rounded-bl-full -mr-4 -mt-4 transition group-hover:scale-110"></div>
                
                <div class="w-14 h-14 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-3xl mb-6 relative z-10 group-hover:bg-green-600 group-hover:text-white transition">
                    <i class="ph-fill ph-headset"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-xl mb-3 relative z-10">Suporte Humanizado</h3>
                <p class="text-gray-500 leading-relaxed text-sm relative z-10">
                    Dúvidas? A nossa equipa está disponível via WhatsApp, telefone e email para ajudar na escolha do produto ideal.
                </p>
            </div>
        </div>

        <div class="mb-24">
            <div class="text-center mb-10">
                <span class="text-zandu-red font-bold tracking-wider text-xs uppercase mb-2 block">Logística</span>
                <h2 class="text-3xl font-bold text-gray-900">Cobertura de Entregas</h2>
                <p class="text-gray-500 mt-2">Consulte os prazos e taxas para a sua província.</p>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200 shadow-lg overflow-hidden max-w-4xl mx-auto">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-8 py-5 text-gray-900 font-extrabold uppercase text-xs tracking-wider">Província</th>
                                <th class="px-8 py-5 text-gray-900 font-extrabold uppercase text-xs tracking-wider">Prazo Estimado</th>
                                <th class="px-8 py-5 text-gray-900 font-extrabold uppercase text-xs tracking-wider text-right">Valor do Envio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($shippingRates as $rate)
                                <tr class="hover:bg-blue-50/30 transition duration-200 group">
                                    <td class="px-8 py-5 font-bold text-gray-900 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:text-zandu-red transition">
                                            <i class="ph-fill ph-map-pin"></i>
                                        </div>
                                        {{ $rate->province_name }}
                                    </td>
                                    <td class="px-8 py-5 text-gray-600 font-medium">{{ $rate->estimated_delivery_time }}</td>
                                    <td class="px-8 py-5 text-right">
                                        <span class="bg-gray-100 text-gray-900 py-1 px-3 rounded-full font-bold text-xs group-hover:bg-zandu-red group-hover:text-white transition">
                                            Kz {{ number_format($rate->rate, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-8 py-12 text-center text-gray-500">
                                        <div class="flex flex-col items-center gap-2">
                                            <i class="ph-duotone ph-info text-2xl text-gray-400"></i>
                                            <p>As taxas de entrega estão a ser atualizadas.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="max-w-3xl mx-auto" x-data="{ active: 1 }">
            <div class="text-center mb-10">
                <h2 class="text-3xl font-bold text-gray-900">Dúvidas Frequentes</h2>
                <p class="text-gray-500 mt-2">Respostas rápidas para as suas perguntas.</p>
            </div>

            <div class="space-y-4">
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300" :class="active === 1 ? 'shadow-lg ring-1 ring-gray-200' : 'shadow-sm'">
                    <button @click="active === 1 ? active = null : active = 1" class="w-full flex justify-between items-center p-6 text-left hover:bg-gray-50 transition">
                        <span class="font-bold text-gray-900 flex items-center gap-3">
                            <i class="ph-fill ph-seal-check text-zandu-red text-xl"></i>
                            Os produtos são originais e selados?
                        </span>
                        <i class="ph-bold ph-caret-down transition-transform duration-300 text-gray-400" :class="active === 1 ? 'rotate-180 text-zandu-red' : ''"></i>
                    </button>
                    <div x-show="active === 1" x-collapse class="border-t border-gray-100">
                        <div class="p-6 text-gray-600 leading-relaxed bg-gray-50/30">
                            Sim. Todos os produtos comercializados na nossa loja são novos, selados na caixa original e acompanham todos os acessórios de fábrica. Garantimos a autenticidade de cada item com selo de garantia oficial.
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300" :class="active === 2 ? 'shadow-lg ring-1 ring-gray-200' : 'shadow-sm'">
                    <button @click="active === 2 ? active = null : active = 2" class="w-full flex justify-between items-center p-6 text-left hover:bg-gray-50 transition">
                        <span class="font-bold text-gray-900 flex items-center gap-3">
                            <i class="ph-fill ph-wallet text-blue-600 text-xl"></i>
                            Quais são as formas de pagamento?
                        </span>
                        <i class="ph-bold ph-caret-down transition-transform duration-300 text-gray-400" :class="active === 2 ? 'rotate-180 text-zandu-red' : ''"></i>
                    </button>
                    <div x-show="active === 2" x-collapse class="border-t border-gray-100">
                        <div class="p-6 text-gray-600 leading-relaxed bg-gray-50/30">
                            Aceitamos Pagamento por Referência (Multicaixa Express), Transferência Bancária (BAI, BFA, BIC) e, para entregas em Luanda, disponibilizamos a opção de pagamento na entrega (Numerário ou TPA).
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300" :class="active === 3 ? 'shadow-lg ring-1 ring-gray-200' : 'shadow-sm'">
                    <button @click="active === 3 ? active = null : active = 3" class="w-full flex justify-between items-center p-6 text-left hover:bg-gray-50 transition">
                        <span class="font-bold text-gray-900 flex items-center gap-3">
                            <i class="ph-fill ph-shield-warning text-orange-500 text-xl"></i>
                            Como funciona a garantia?
                        </span>
                        <i class="ph-bold ph-caret-down transition-transform duration-300 text-gray-400" :class="active === 3 ? 'rotate-180 text-zandu-red' : ''"></i>
                    </button>
                    <div x-show="active === 3" x-collapse class="border-t border-gray-100">
                        <div class="p-6 text-gray-600 leading-relaxed bg-gray-50/30">
                            Oferecemos garantia de 12 meses contra defeitos de fabrico para a maioria dos eletrónicos. Caso tenha algum problema, basta contactar o nosso suporte com a sua fatura de compra e o número de série do equipamento.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-24 text-center bg-gray-900 rounded-[2.5rem] p-12 relative overflow-hidden shadow-2xl">
            <div class="absolute top-0 left-0 w-full h-full opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')]"></div>
            <div class="relative z-10">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Encontre o que precisa hoje</h2>
                <p class="text-gray-400 mb-8 text-lg">Visite a nossa loja e descubra ofertas imperdíveis em tecnologia.</p>
                <a href="{{ route('shop') }}" class="inline-flex items-center gap-3 bg-white text-gray-900 font-bold px-10 py-4 rounded-full hover:bg-gray-100 transition shadow-lg transform hover:scale-105 duration-300">
                    Ir para a Loja <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>
</div>