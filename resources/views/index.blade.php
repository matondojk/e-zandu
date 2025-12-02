<!DOCTYPE html>
<html lang="pt-AO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zandu - Eletrônicos & Tecnologia</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Phosphor Icons (Alternativa moderna ao Lucide/FontAwesome) -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <!-- Configurações de Estilo Personalizado -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        zandu: {
                            red: '#E61E2A',     // Vermelho Zandu
                            dark: '#1A1A1A',    // Preto Suave
                            light: '#F9FAFB',   // Fundo claro
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'float-delayed': 'float 6s ease-in-out 3s infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Estilos adicionais para scrollbar e detalhes finos */
        body {
            -webkit-font-smoothing: antialiased;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- NAVBAR -->
    <nav class="fixed w-full z-50 transition-all duration-300" id="navbar">
        <div class="bg-white/90 backdrop-blur-md shadow-sm border-b border-gray-100">
            <div class="container mx-auto px-4 md:px-6 h-20 flex items-center justify-between">
                
                <!-- Logo -->
                <a href="#" class="flex-shrink-0">
                    <img src="https://e-zandu.com/default/logo-dark.png" alt="Zandu Logo" class="h-10 w-auto object-contain hover:opacity-90 transition">
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8 font-semibold text-sm tracking-wide">
                    <a href="#" class="text-zandu-red">Início</a>
                    <a href="#shop" class="text-gray-600 hover:text-zandu-red transition">Loja</a>
                    <a href="#about" class="text-gray-600 hover:text-zandu-red transition">Sobre Nós</a>
                    <a href="#support" class="text-gray-600 hover:text-zandu-red transition">Suporte</a>
                </div>

                <!-- Icons Actions -->
                <div class="flex items-center space-x-2 md:space-x-4">
                    <button class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition">
                        <i class="ph ph-magnifying-glass text-2xl"></i>
                    </button>
                    <button class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition">
                        <i class="ph ph-user text-2xl"></i>
                    </button>
                    <button class="p-2 hover:bg-gray-100 rounded-full text-gray-600 transition relative group">
                        <i class="ph ph-shopping-cart text-2xl"></i>
                        <span class="absolute top-1 right-1 bg-zandu-red text-white text-[10px] font-bold h-4 w-4 flex items-center justify-center rounded-full">2</span>
                    </button>
                    
                    <!-- Mobile Menu Button -->
                    <button class="md:hidden p-2 text-gray-700" onclick="toggleMobileMenu()">
                        <i class="ph ph-list text-2xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100 absolute w-full shadow-lg">
            <div class="flex flex-col p-4 space-y-4">
                <a href="#" class="text-zandu-red font-semibold">Início</a>
                <a href="#" class="text-gray-700 font-medium">Loja</a>
                <a href="#" class="text-gray-700 font-medium">Sobre Nós</a>
                <a href="#" class="text-gray-700 font-medium">Suporte</a>
                <hr>
                <a href="#" class="text-gray-500 text-sm">Minha Conta</a>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <header class="relative bg-zandu-red pt-32 pb-20 md:pt-40 md:pb-32 overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -left-[10%] w-[50vw] h-[50vw] bg-white opacity-10 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-[10%] right-[5%] w-[30vw] h-[30vw] bg-orange-400 opacity-20 rounded-full blur-[80px] mix-blend-overlay"></div>
        </div>

        <div class="container mx-auto px-4 md:px-6 relative z-10">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
                
                <!-- Hero Text -->
                <div class="w-full lg:w-1/2 text-white text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur-sm px-4 py-1.5 rounded-full text-sm font-medium mb-6 text-white">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        Nova Coleção 2024 Disponível
                    </div>
                    <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight mb-6 tracking-tight">
                        O futuro da <br/>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-orange-300">Tecnologia</span>
                        está aqui.
                    </h1>
                    <p class="text-lg md:text-xl text-white/80 mb-10 max-w-xl mx-auto lg:mx-0 font-light leading-relaxed">
                        Descubra eletrônicos de ponta com preços imbatíveis. Entregamos em toda Angola com garantia e segurança.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="#shop" class="bg-white text-zandu-red px-8 py-4 rounded-full font-bold text-lg hover:bg-gray-50 transform hover:-translate-y-1 transition shadow-xl flex items-center justify-center gap-2">
                            Comprar Agora <i class="ph-bold ph-arrow-right"></i>
                        </a>
                        <a href="#about" class="px-8 py-4 rounded-full font-bold text-lg text-white border-2 border-white/30 hover:bg-white/10 transition flex items-center justify-center">
                            Saiba Mais
                        </a>
                    </div>
                </div>

                <!-- Hero Image Composition -->
                <div class="w-full lg:w-1/2 relative h-[400px] md:h-[500px] flex items-center justify-center perspective-1000">
                    <!-- Main Circle Image -->
                    <div class="relative z-20 w-64 h-64 md:w-[400px] md:h-[400px] bg-white rounded-full p-2 shadow-2xl border-4 border-white/20">
                        <div class="w-full h-full rounded-full overflow-hidden bg-gray-100 relative group">
                            <img src="https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&q=80&w=800" 
                                 alt="Happy Customer with Tech" 
                                 class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700">
                            <!-- Gradient Overlay -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                    </div>

                    <!-- Floating Elements -->
                    <!-- Floating Card 1 (Phone) -->
                    <div class="absolute top-10 md:top-0 right-4 md:right-10 z-30 bg-white p-3 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] animate-float w-32 md:w-40 transform rotate-6">
                        <div class="bg-gray-50 rounded-xl p-2 mb-2">
                            <img src="https://images.unsplash.com/photo-1592750475338-afd86943583d?auto=format&fit=crop&q=80&w=200" alt="Product" class="w-full h-auto object-contain">
                        </div>
                        <div class="px-1">
                            <div class="h-2 w-2/3 bg-gray-200 rounded mb-1"></div>
                            <div class="h-2 w-1/2 bg-zandu-red rounded"></div>
                        </div>
                    </div>

                    <!-- Floating Card 2 (Watch) -->
                    <div class="absolute bottom-0 left-4 md:left-10 z-30 bg-white p-3 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] animate-float-delayed w-28 md:w-36 transform -rotate-6">
                        <div class="bg-gray-50 rounded-xl p-2 mb-2">
                            <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&q=80&w=200" alt="Watch" class="w-full h-auto object-contain">
                        </div>
                        <div class="px-1">
                            <div class="h-2 w-3/4 bg-gray-200 rounded mb-1"></div>
                            <div class="h-2 w-1/3 bg-zandu-red rounded"></div>
                        </div>
                    </div>

                    <!-- Decorative Elements -->
                    <div class="absolute top-1/2 left-0 -translate-y-1/2 -translate-x-6 bg-yellow-400 w-12 h-12 rounded-full flex items-center justify-center shadow-lg z-10 animate-bounce">
                        <i class="ph-fill ph-star text-white text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Curved Bottom Divider (Refined) -->
        <div class="absolute bottom-0 left-0 w-full leading-none overflow-hidden">
            <svg class="relative block w-[calc(100%+1.3px)] h-[60px] md:h-[120px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" class="fill-gray-50"></path>
            </svg>
        </div>
    </header>

    <!-- SECTION 1: CATEGORIAS (Abaixo do Hero - NOVO DESIGN) -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Explore por Categorias</h2>
                <p class="text-gray-500">Encontre exatamente o que você precisa.</p>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <!-- Categoria 1 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-red-50 text-zandu-red rounded-full flex items-center justify-center mb-4 group-hover:bg-zandu-red group-hover:text-white transition duration-300">
                        <i class="ph ph-device-mobile text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-zandu-red transition">Celulares</span>
                </a>
                
                <!-- Categoria 2 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition duration-300">
                        <i class="ph ph-laptop text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-blue-600 transition">Computadores</span>
                </a>

                <!-- Categoria 3 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition duration-300">
                        <i class="ph ph-headphones text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-purple-600 transition">Áudio</span>
                </a>

                <!-- Categoria 4 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-green-50 text-green-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition duration-300">
                        <i class="ph ph-watch text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-green-600 transition">Smartwatch</span>
                </a>

                <!-- Categoria 5 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-orange-600 group-hover:text-white transition duration-300">
                        <i class="ph ph-game-controller text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-orange-600 transition">Games</span>
                </a>

                <!-- Categoria 6 -->
                <a href="#" class="group bg-white rounded-2xl p-6 flex flex-col items-center justify-center shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 border border-gray-100">
                    <div class="w-14 h-14 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-gray-800 group-hover:text-white transition duration-300">
                        <i class="ph ph-camera text-2xl"></i>
                    </div>
                    <span class="font-semibold text-gray-800 group-hover:text-gray-900 transition">Câmeras</span>
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION 2: DESTAQUES / PRODUTOS (Grid Bonito) -->
    <section id="shop" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-end mb-10">
                <div>
                    <span class="text-zandu-red font-bold tracking-wider text-sm uppercase">Selecionados para você</span>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2">Mais Vendidos da Semana</h2>
                </div>
                <a href="#" class="hidden md:flex items-center gap-2 text-gray-500 hover:text-zandu-red transition font-medium">
                    Ver todos <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>

            <!-- Product Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <!-- Product Card 1 -->
                <div class="group relative bg-white rounded-2xl border border-gray-100 hover:shadow-2xl hover:border-transparent transition-all duration-300">
                    <div class="relative h-64 bg-gray-50 rounded-t-2xl p-8 flex items-center justify-center overflow-hidden">
                        <span class="absolute top-4 left-4 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-1 rounded-full z-10">HOT</span>
                        <button class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-400 hover:text-red-500 hover:scale-110 transition z-10">
                            <i class="ph-fill ph-heart"></i>
                        </button>
                        <img src="https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&q=80&w=400" alt="iPhone" class="w-full h-full object-contain transform group-hover:scale-110 transition duration-500 mix-blend-multiply">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-1 text-yellow-400 mb-2 text-sm">
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <span class="text-gray-400 text-xs ml-1">(45)</span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-900 mb-1 line-clamp-1 group-hover:text-zandu-red transition">iPhone 15 Pro Max</h3>
                        <p class="text-sm text-gray-500 mb-4">256GB - Titanium Natural</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-gray-400 line-through">Kz 1.200.000</span>
                                <span class="block text-xl font-bold text-zandu-red">Kz 899.000</span>
                            </div>
                            <button class="bg-gray-900 text-white p-3 rounded-xl hover:bg-zandu-red transition shadow-lg transform active:scale-95">
                                <i class="ph ph-shopping-cart text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Card 2 -->
                <div class="group relative bg-white rounded-2xl border border-gray-100 hover:shadow-2xl hover:border-transparent transition-all duration-300">
                    <div class="relative h-64 bg-gray-50 rounded-t-2xl p-8 flex items-center justify-center overflow-hidden">
                        <button class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-400 hover:text-red-500 hover:scale-110 transition z-10">
                            <i class="ph-fill ph-heart"></i>
                        </button>
                        <img src="https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?auto=format&fit=crop&q=80&w=400" alt="Headphone" class="w-full h-full object-contain transform group-hover:scale-110 transition duration-500 mix-blend-multiply">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-1 text-yellow-400 mb-2 text-sm">
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-half-fill ph-star"></i>
                            <span class="text-gray-400 text-xs ml-1">(12)</span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-900 mb-1 line-clamp-1 group-hover:text-zandu-red transition">Sony WH-1000XM5</h3>
                        <p class="text-sm text-gray-500 mb-4">Cancelamento de Ruído</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xl font-bold text-zandu-red">Kz 189.000</span>
                            </div>
                            <button class="bg-gray-900 text-white p-3 rounded-xl hover:bg-zandu-red transition shadow-lg transform active:scale-95">
                                <i class="ph ph-shopping-cart text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                 <!-- Product Card 3 -->
                 <div class="group relative bg-white rounded-2xl border border-gray-100 hover:shadow-2xl hover:border-transparent transition-all duration-300">
                    <div class="relative h-64 bg-gray-50 rounded-t-2xl p-8 flex items-center justify-center overflow-hidden">
                        <span class="absolute top-4 left-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full z-10">-15%</span>
                        <button class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-400 hover:text-red-500 hover:scale-110 transition z-10">
                            <i class="ph-fill ph-heart"></i>
                        </button>
                        <img src="https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&q=80&w=400" alt="Macbook" class="w-full h-full object-contain transform group-hover:scale-110 transition duration-500 mix-blend-multiply">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-1 text-yellow-400 mb-2 text-sm">
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <span class="text-gray-400 text-xs ml-1">(89)</span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-900 mb-1 line-clamp-1 group-hover:text-zandu-red transition">MacBook Air M2</h3>
                        <p class="text-sm text-gray-500 mb-4">13" Midnight - 8GB</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xs text-gray-400 line-through">Kz 950.000</span>
                                <span class="block text-xl font-bold text-zandu-red">Kz 749.000</span>
                            </div>
                            <button class="bg-gray-900 text-white p-3 rounded-xl hover:bg-zandu-red transition shadow-lg transform active:scale-95">
                                <i class="ph ph-shopping-cart text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>

                 <!-- Product Card 4 -->
                 <div class="group relative bg-white rounded-2xl border border-gray-100 hover:shadow-2xl hover:border-transparent transition-all duration-300">
                    <div class="relative h-64 bg-gray-50 rounded-t-2xl p-8 flex items-center justify-center overflow-hidden">
                        <button class="absolute top-4 right-4 bg-white p-2 rounded-full shadow-md text-gray-400 hover:text-red-500 hover:scale-110 transition z-10">
                            <i class="ph-fill ph-heart"></i>
                        </button>
                        <img src="https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&q=80&w=400" alt="Watch" class="w-full h-full object-contain transform group-hover:scale-110 transition duration-500 mix-blend-multiply">
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-1 text-yellow-400 mb-2 text-sm">
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-fill ph-star"></i>
                            <i class="ph-bold ph-star"></i>
                            <span class="text-gray-400 text-xs ml-1">(22)</span>
                        </div>
                        <h3 class="font-bold text-lg text-gray-900 mb-1 line-clamp-1 group-hover:text-zandu-red transition">Apple Watch Ultra</h3>
                        <p class="text-sm text-gray-500 mb-4">Titanium - Orange Loop</p>
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-xl font-bold text-zandu-red">Kz 459.000</span>
                            </div>
                            <button class="bg-gray-900 text-white p-3 rounded-xl hover:bg-zandu-red transition shadow-lg transform active:scale-95">
                                <i class="ph ph-shopping-cart text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

             <div class="mt-10 text-center md:hidden">
                <a href="#" class="inline-flex items-center gap-2 text-zandu-red font-bold border border-zandu-red px-6 py-3 rounded-full hover:bg-red-50 transition">
                    Ver todos os produtos <i class="ph-bold ph-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- SECTION 3: BANNER PROMOCIONAL LARGO -->
    <section class="py-10">
        <div class="container mx-auto px-4">
            <div class="relative rounded-3xl overflow-hidden bg-black text-white h-[300px] md:h-[400px] flex items-center">
                <!-- Background Image -->
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&q=80&w=1200" class="w-full h-full object-cover opacity-60" alt="Promo Banner">
                </div>
                
                <div class="relative z-10 px-8 md:px-16 max-w-2xl">
                    <span class="bg-zandu-red text-white text-xs font-bold px-3 py-1 rounded uppercase tracking-wide mb-4 inline-block">Oferta Limitada</span>
                    <h2 class="text-3xl md:text-5xl font-bold mb-6">Realidade Virtual <br> Próxima Geração</h2>
                    <p class="text-gray-300 mb-8 text-lg">Mergulhe em novos mundos com o novo Zandu VR Headset. Tecnologia imersiva como você nunca viu.</p>
                    <button class="bg-white text-black px-8 py-3 rounded-full font-bold hover:bg-gray-200 transition flex items-center gap-2">
                        Comprar Agora <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION 4: BENEFÍCIOS (Mantendo o estilo escuro de confiança) -->
    <section class="bg-gray-900 text-white py-20">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-gray-800/30 hover:bg-gray-800 transition duration-300">
                    <div class="w-16 h-16 bg-zandu-red rounded-full flex items-center justify-center text-3xl mb-6 shadow-lg shadow-red-900/50">
                        <i class="ph-fill ph-truck"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Entrega Expressa</h3>
                    <p class="text-gray-400 leading-relaxed">Receba seus produtos em até 24 horas em Luanda e 3 dias para as demais províncias.</p>
                </div>

                <!-- Feature 2 -->
                <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-gray-800/30 hover:bg-gray-800 transition duration-300">
                    <div class="w-16 h-16 bg-zandu-red rounded-full flex items-center justify-center text-3xl mb-6 shadow-lg shadow-red-900/50">
                        <i class="ph-fill ph-shield-check"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Garantia Oficial</h3>
                    <p class="text-gray-400 leading-relaxed">Compre com tranquilidade. Todos os produtos possuem garantia de 12 meses.</p>
                </div>

                <!-- Feature 3 -->
                <div class="flex flex-col items-center text-center p-6 rounded-2xl bg-gray-800/30 hover:bg-gray-800 transition duration-300">
                    <div class="w-16 h-16 bg-zandu-red rounded-full flex items-center justify-center text-3xl mb-6 shadow-lg shadow-red-900/50">
                        <i class="ph-fill ph-credit-card"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Pagamento Seguro</h3>
                    <p class="text-gray-400 leading-relaxed">Aceitamos Multicaixa Express, Transferência Bancária e Pagamento na Entrega.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-white pt-16 pb-8 border-t border-gray-100 text-sm">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-12">
                <!-- Brand Info -->
                <div>
                    <img src="https://e-zandu.com/default/logo-dark.png" alt="Zandu Logo" class="h-8 mb-6">
                    <p class="text-gray-500 leading-relaxed mb-6">
                        A Zandu é a sua loja de eletrônicos de confiança em Angola. Trazendo inovação e tecnologia para o seu dia a dia com os melhores preços.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-zandu-red hover:text-white transition">
                            <i class="ph-fill ph-facebook-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-zandu-red hover:text-white transition">
                            <i class="ph-fill ph-instagram-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 hover:bg-zandu-red hover:text-white transition">
                            <i class="ph-fill ph-twitter-logo text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Links Rápidos -->
                <div>
                    <h4 class="font-bold text-gray-900 text-lg mb-6">Navegação</h4>
                    <ul class="space-y-3 text-gray-600">
                        <li><a href="#" class="hover:text-zandu-red transition">Início</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Loja</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Sobre Nós</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Blog</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Contato</a></li>
                    </ul>
                </div>

                <!-- Suporte -->
                <div>
                    <h4 class="font-bold text-gray-900 text-lg mb-6">Ajuda</h4>
                    <ul class="space-y-3 text-gray-600">
                        <li><a href="#" class="hover:text-zandu-red transition">Minha Conta</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Rastrear Pedido</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Políticas de Devolução</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">Termos e Condições</a></li>
                        <li><a href="#" class="hover:text-zandu-red transition">FAQ</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="font-bold text-gray-900 text-lg mb-6">Fique por dentro</h4>
                    <p class="text-gray-500 mb-4">Cadastre seu email para receber ofertas exclusivas.</p>
                    <form class="flex flex-col gap-3">
                        <input type="email" placeholder="Seu melhor email" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-zandu-red/50 w-full">
                        <button type="submit" class="bg-zandu-red text-white font-bold py-3 rounded-lg hover:bg-red-700 transition w-full">
                            Inscrever-se
                        </button>
                    </form>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-8 flex flex-col md:flex-row justify-between items-center text-gray-400">
                <p>&copy; 2024 Zandu Comércio Geral. Todos os direitos reservados.</p>
                <div class="flex gap-4 mt-4 md:mt-0">
                    <span class="hover:text-gray-600 cursor-pointer">Privacidade</span>
                    <span class="hover:text-gray-600 cursor-pointer">Termos</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Script -->
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        }

        // Simple Navbar Scroll Effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('shadow-md');
            } else {
                navbar.classList.remove('shadow-md');
            }
        });
    </script>
</body>
</html>