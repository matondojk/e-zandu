<!DOCTYPE html>
<html lang="pt-AO" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Zandu - Eletrónica & Tecnologia' }}</title>
    <meta name="description"
        content="{{ $description ?? 'A melhor loja de eletrónicos e tecnologia de Angola. Entregas rápidas e garantia oficial.' }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ?? 'Zandu - Eletrónica & Tecnologia' }}">
    <meta property="og:description"
        content="{{ $description ?? 'Encontre os melhores produtos tecnológicos com entrega rápida em Angola.' }}">
    <meta property="og:image" content="{{ $image ?? 'https://e-zandu.com/default/logo-dark.png' }}">

    <link rel="icon" href="https://e-zandu.com/default/icon-default.png" type="image/png">

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        zandu: {
                            red: '#E61E2A',
                            dark: '#1A1A1A',
                            light: '#F9FAFB',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        // Removi o pulse-slow pois agora usaremos o bounce nativo
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-20px)'
                            },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            -webkit-font-smoothing: antialiased;
        }

        [x-cloak] {
            display: none !important;
        }

        .swal2-popup.zandu-toast {
            padding: 0.75rem 1rem !important;
            border-radius: 1rem !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.1) !important;
            display: flex !important;
            align-items: center !important;
        }

        .swal2-title.zandu-title {
            font-family: 'Inter', sans-serif !important;
            font-size: 0.9rem !important;
            font-weight: 600 !important;
            color: #1f2937 !important;
        }

        nav[role="navigation"] span[aria-current="page"] span {
            background-color: #E61E2A !important;
            border-color: #E61E2A !important;
            color: white !important;
        }

        nav[role="navigation"] a:hover {
            color: #E61E2A !important;
        }
        
        /* Efeito Hover do Botão WhatsApp */
        .whatsapp-float:hover .whatsapp-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }
    </style>
    @livewireStyles
</head>

<body class="bg-gray-50 text-gray-800 font-sans pt-[120px]">

    <livewire:site.defaults.header />

    <main>
        {{ $slot }}
    </main>

    @include('livewire.site.defaults.footer')

    <div class="fixed bottom-6 right-6 z-50 group whatsapp-float">
        <div class="whatsapp-tooltip absolute right-full mr-3 top-1/2 -translate-y-1/2 bg-white text-gray-800 px-4 py-2 rounded-lg shadow-lg text-sm font-bold whitespace-nowrap opacity-0 invisible translate-x-4 transition-all duration-300 pointer-events-none">
            Fale connosco no WhatsApp!
            <div class="absolute top-1/2 -right-1.5 -translate-y-1/2 w-3 h-3 bg-white transform rotate-45"></div>
        </div>

        <a href="https://wa.me/15556491355?text=Ol%C3%A1!%20Gostaria%20de%20saber%20mais%20sobre%20os%20produtos." 
           target="_blank" 
           class="flex items-center justify-center w-14 h-14 bg-[#25D366] rounded-full text-white shadow-xl shadow-green-500/30 hover:bg-[#20ba5a] hover:scale-110 transition-all duration-300 animate-bounce">
            <i class="ph-fill ph-whatsapp-logo text-3xl"></i>
        </a>
    </div>

    @livewireScripts
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Notificação Suave e Moderna
        window.addEventListener('toast', event => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-right',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'zandu-toast',
                    title: 'zandu-title'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: event.detail[0].type, // 'success' ou 'error'
                title: event.detail[0].message,
                iconColor: event.detail[0].type === 'success' ? '#10B981' : '#EF4444',
            });
        });
    </script>
</body>

</html>