<footer class="bg-[#111] pt-16 pb-8 border-t border-gray-800 text-sm font-sans">
    <div class="container mx-auto px-4">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 lg:gap-12 mb-12">
            
            <div class="lg:col-span-2">
                <a href="/" class="block mb-6">
                    <img src="https://e-zandu.com/default/logo-light.png" alt="Zandu Logo" class="h-8">
                </a>
                <p class="text-gray-400 leading-relaxed mb-6 pr-4">
                    A E-Zandu é a sua plataforma de referência para tecnologia e inovação em Angola. 
                    Conectamos você aos melhores produtos com inteligência e rapidez.
                </p>
                
                <div class="flex gap-4">
                    <a href="https://facebook.com/ezandu" target="_blank" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-zandu-red hover:text-white transition-all duration-300">
                        <i class="ph-fill ph-facebook-logo text-xl"></i>
                    </a>
                    <a href="https://instagram.com/ezandu" target="_blank" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-zandu-red hover:text-white transition-all duration-300">
                        <i class="ph-fill ph-instagram-logo text-xl"></i>
                    </a>
                    <a href="https://wa.me/244923111222" target="_blank" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-green-600 hover:text-white transition-all duration-300">
                        <i class="ph-fill ph-whatsapp-logo text-xl"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-bold text-white text-lg mb-6">Institucional</h4>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Sobre Nós</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Carreiras</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Blog Oficial</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Parceiros</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-white text-lg mb-6">Ajuda & Suporte</h4>
                <ul class="space-y-3 text-gray-400">
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Central de Ajuda</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Termos de Uso</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Política de Privacidade</a></li>
                    <li><a href="#" class="hover:text-zandu-red transition-colors">Trocas e Devoluções</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-bold text-white text-lg mb-6">Fique por dentro</h4>
                <p class="text-gray-500 mb-4 text-xs">Receba ofertas exclusivas e novidades tecnológicas.</p>
                
                <form class="space-y-2">
                    <input type="email" placeholder="Seu melhor email" class="w-full bg-gray-800 border border-gray-700 text-white p-3 rounded-lg text-sm focus:outline-none focus:border-zandu-red transition-colors placeholder-gray-500">
                    <button type="submit" class="w-full bg-zandu-red text-white py-3 rounded-lg font-bold hover:bg-red-700 transition shadow-lg shadow-red-900/20">
                        Subscrever
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-xs">
                &copy; {{ date('Y') }} E-Zandu. Todos os direitos reservados. Feito por Matondo João.
            </p>
            
            <div class="flex gap-3 opacity-50 grayscale hover:grayscale-0 transition-all duration-500">
                <i class="ph-fill ph-credit-card text-2xl text-gray-400"></i>
                <i class="ph-fill ph-bank text-2xl text-gray-400"></i>
            </div>
        </div>
    </div>
</footer>