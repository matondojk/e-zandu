<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento Confirmado - Pedido #{{ $cart->id }}</title>
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'zandu-red': '#DC2626',
                        'success-green': '#059669',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 font-sans text-gray-900">

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">
        
        <div class="bg-green-50 p-8 flex flex-col items-center justify-center text-center border-b border-green-100">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4 shadow-sm">
                <i class="ph-fill ph-check-circle text-5xl text-green-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Pagamento Recebido!</h1>
            <p class="text-green-700 font-medium mt-1">Transação concluída com sucesso</p>
        </div>

        <div class="p-8 space-y-6">
            
            <div class="text-center">
                <p class="text-gray-600 mb-4">
                    Obrigado! O seu pagamento referente ao pedido abaixo foi processado.
                </p>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex flex-col items-center">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Número do Pedido</span>
                    <span class="text-2xl font-extrabold text-gray-900">#{{ $cart->id }}</span>
                </div>
            </div>

            <div class="flex items-start gap-3 bg-blue-50 p-4 rounded-xl border border-blue-100">
                <i class="ph-fill ph-whatsapp-logo text-2xl text-green-600 mt-0.5"></i>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Próximos Passos</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Enviámos a fatura final e os detalhes de acompanhamento para o seu WhatsApp.
                    </p>
                </div>
            </div>

            <div class="space-y-3">
                <a href="/" class="block w-full bg-gray-900 hover:bg-gray-800 text-white font-bold py-3.5 rounded-xl transition text-center shadow-lg">
                    Voltar à Loja
                </a>
                <button onclick="window.close()" class="block w-full text-gray-500 hover:text-gray-700 font-semibold text-sm py-2 transition">
                    Fechar esta janela
                </button>
            </div>

        </div>
    </div>

</body>
</html>