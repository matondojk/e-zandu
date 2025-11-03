{{-- resources/views/components/footer.blade.php --}}

<footer class="bg-gray-800 text-gray-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <div>
                <h3 class="text-xl font-bold text-white mb-4">E-Zandu</h3>
                <p class="text-gray-400">
                    Sua loja online de confiança, trazendo os melhores produtos com a maior conveniência.
                </p>
                <div class="flex space-x-4 mt-6">
                    <a href="#" class="text-gray-400 hover:text-orange-500">
                        <span class="sr-only">Facebook</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-orange-500">
                        <span class="sr-only">Instagram</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"></svg>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-orange-500">
                        <span class="sr-only">WhatsApp</span>
                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"></svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Loja</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-orange-500">Categorias</a></li>
                    <li><a href="#" class="hover:text-orange-500">Promoções</a></li>
                    <li><a href="#" class="hover:text-orange-500">Novidades</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Institucional</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="hover:text-orange-500">Sobre Nós</a></li>
                    <li><a href="#" class="hover:text-orange-500">Política de Privacidade</a></li>
                    <li><a href="#" class="hover:text-orange-500">Termos de Uso</a></li>
                    <li><a href="#" class="hover:text-orange-500">FAQ</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-semibold text-white mb-4">Contato</h4>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-start">
                        <span class="mt-1 mr-2"></span>
                        <span>Rua Exemplo, 123, Cidade, País</span>
                    </li>
                    <li class="flex items-center">
                        <span class="mr-2"></span>
                        <span>contato@e-zandu.com</span>
                    </li>
                    <li class="flex items-center">
                        <span class="mr-2"></span>
                        <span>(XX) 99999-9999</span>
                    </li>
                </ul>
            </div>

        </div>

        <div class="mt-10 pt-8 border-t border-gray-700 text-center">
            <p class="text-gray-400">&copy; {{ date('Y') }} E-Zandu. Todos os direitos reservados.</p>
        </div>
    </div>
</footer>