<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use \Exception;
use \stdClass;

// Imports de Modelos
use App\Models\Setting;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\PaymentMethod; // ✅ IMPORTADO
use App\Models\ShippingRate;  // ✅ IMPORTADO
use App\Models\Coupon;         // ✅ IMPORTADO

// Imports de Suporte
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessWhatsAppMessage; 

class WhatsAppWebhookController extends Controller
{
    protected $orderService;
    protected $whatsAppService;

    public function __construct(OrderService $orderService, WhatsAppService $whatsAppService)
    {
        $this->orderService = $orderService;
        $this->whatsAppService = $whatsAppService;
    }

    // ... (verify - mantido igual)
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');
        $verifyToken = config('services.whatsapp.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Webhook verificado com sucesso!');
            return response($challenge, 200);
        }
        Log::warning('Falha na verificação do Webhook. Token recebido: ' . $token);
        return response('Forbidden', 403);
    }

    /**
     * Recebe TODAS as mensagens do usuário (Router Principal)
     */
    public function handle(Request $request)
    {
        Log::info('Mensagem recebida:');
        Log::info(json_encode($request->all(), JSON_PRETTY_PRINT));

        try {
            $messagePayload = $request->input('entry.0.changes.0.value');
            if (empty($messagePayload['messages'])) {
                return response('OK', 200); // Resposta vazia, ignora
            }

            $messageData = $messagePayload['messages'][0];
            $userPhone = $messageData['from'];
            $messageType = $messageData['type'];
            $userName = $messagePayload['contacts'][0]['profile']['name'] ?? null;

            // 1. Encontra ou Cria o Cliente (Isto é rápido)
            $customer = $this->getOrCreateCustomer($userPhone, $userName);

            // 2. Dispara o Job para processar a mensagem EM BACKGROUND
            ProcessWhatsAppMessage::dispatch($customer, $messageData, $messageType);

            // 3. Responde OK IMEDIATAMENTE
            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('Erro grave no handle() do Webhook (antes de disparar o Job): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response('OK', 200); // Responde 200 mesmo assim para evitar retentativas
        }
    }

    // --- Lógica de Identificação/Criação do Cliente ---
    protected function getOrCreateCustomer(string $userPhone, ?string $userName = null): Customer
    {
        $customer = Customer::firstOrCreate(
            ['phone' => $userPhone],
            [
                'password' => Hash::make($userPhone),
                'name' => $userName,
            ]
        );

        if ($customer->wasRecentlyCreated === false && empty($customer->name) && !empty($userName)) {
            $customer->update(['name' => $userName]);
        }

        return $customer;
    }


    // ===================================================================
    // PROCESSADOR DE MENSAGEM DE TEXTO (LÓGICA DA OPENAI)
    // ===================================================================

    public function processTextMessage(Customer $customer, string $userMessage)
    {
        $userPhone = $customer->phone;
        Log::info("PROCESSANDO MENSAGEM de [{$userPhone}]: {$userMessage}");

        // --- CHAMADA PARA OPENAI ---
        $systemPrompt = $this->buildOpenAISystemPrompt();
        $apiKey = config('services.openai.key');

        if (!$apiKey) {
            Log::error("Chave da API OpenAI (OPENAI_API_KEY) não configurada.");
            $this->whatsAppService->sendTextMessage("Desculpe, estou com problemas técnicos (IA_KEY).", $userPhone);
            return;
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(45) 
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage]
                    ],
                    'response_format' => ['type' => 'json_object'] 
                ]);

            if (!$response->successful()) {
                Log::error("Erro da API OpenAI: " . $response->body());
                $this->whatsAppService->sendTextMessage("Desculpe, não consegui processar seu pedido agora (IA_API_FAIL).", $userPhone);
                return;
            }

            $jsonResponse = $response->json('choices.0.message.content');
            $data = json_decode($jsonResponse, true);
            
            if (is_null($data)) {
                Log::error("OpenAI retornou JSON inválido: " . $jsonResponse);
                $this->whatsAppService->sendTextMessage("Desculpe, tive um problema ao processar a resposta (IA_JSON_FAIL).", $userPhone);
                return;
            }

        } catch (Exception $e) {
            Log::error('Erro ao chamar API OpenAI: ' . $e->getMessage());
            $this->whatsAppService->sendTextMessage("Desculpe, estou com um problema de conexão com o assistente.", $userPhone);
            return;
        }
        // --- FIM DA CHAMADA OPENAI ---


        // ✅ MODIFICADO: Lógica de personalização no cumprimento
        if (isset($data['type']) && $data['type'] === 'search') {
            $this->whatsAppService->sendTextMessage($data['introduction'], $userPhone);

            if (empty($data['sql_query']) || !str_starts_with(strtoupper($data['sql_query']), 'SELECT')) {
                Log::warning("OpenAI retornou um 'search' sem um SELECT SQL válido.");
                $this->whatsAppService->sendTextMessage("Desculpe, tive um problema ao buscar isso.", $userPhone);
                return;
            }

            try {
                $results = DB::select($data['sql_query']);
            } catch (Exception $e) {
                Log::error("Erro ao executar SQL da IA: " . $e->getMessage());
                Log::error("SQL com falha: " . $data['sql_query']);
                $this->whatsAppService->sendTextMessage("Desculpe, tive um problema ao buscar os produtos (IA_SQL_FAIL).", $userPhone);
                return;
            }
            
            $productIds = collect($results)->pluck('id');

            if ($productIds->isNotEmpty()) {
                $products = Product::with('images')->whereIn('id', $productIds)->get();

                Log::info("Produtos encontrados: " . $products->count());
                foreach ($products as $product) {
                    $this->sendProductCardMessage($customer, $product);
                }
            } else {
                $setting = Setting::first();
                $types = $setting->product_types ? implode(', ', $setting->product_types) : 'diversos itens';
                $message = "Desculpe, não encontrei produtos que correspondam a essa descrição.\n\n";
                $message .= "Talvez queira pesquisar por algo nos nossos tipos de produtos mais populares: *{$types}*.";
                $this->whatsAppService->sendTextMessage($message, $userPhone);
            }
        } elseif (isset($data['type']) && ($data['type'] === 'greeting' || $data['type'] === 'faq')) {
            if (!empty($data['introduction'])) {
                $intro = $data['introduction'];
                // ✅ Cumprimento Personalizado (Vendedor adora usar o nome!)
                if ($data['type'] === 'greeting' && !empty($customer->name)) {
                    // Substitui a saudação inicial por uma personalizada (ex: "Olá!" -> "Olá, João!")
                    $intro = preg_replace('/(Olá|Bom dia|Boa tarde|Boa noite)\s*!?/i', "$1, *{$customer->name}*! ", $intro, 1) ?? "Olá, *{$customer->name}*! Em que posso ajudar a finalizar sua compra?";
                }
                $this->whatsAppService->sendTextMessage($intro, $userPhone);
            } else {
                Log::warning("OpenAI retornou 'greeting' ou 'faq' sem 'introduction'.");
                $this->whatsAppService->sendTextMessage("Desculpe, não consegui entender.", $userPhone);
            }
        } else {
            Log::warning("OpenAI retornou um JSON inválido ou tipo desconhecido.");
            $this->whatsAppService->sendTextMessage("Desculpe, não consegui entender seu pedido.", $userPhone);
        }
    }

    // ===================================================================
    // HANDLER DE MENSAGEM INTERATIVA (BOTÕES)
    // ===================================================================
    public function handleInteractiveMessage(Customer $customer, string $buttonId)
    {
        Log::info("Interactive message [{$buttonId}] do cliente [{$customer->phone}]");

        if ($buttonId === 'VER_CARRINHO') {
            $this->showCart($customer);
        } elseif ($buttonId === 'FINALIZAR_PEDIDO') {
            $this->finalizeOrder($customer);

        } elseif (Str::startsWith($buttonId, 'ADD_ITEM_') || Str::startsWith($buttonId, 'ADD_CROSS_SELL_')) {

            $productId = Str::afterLast($buttonId, '_');
            $product = Product::find($productId);

            if (!$product) {
                $this->whatsAppService->sendTextMessage("Desculpe, este produto não está mais disponível.", $customer->phone);
                return;
            }

            $cart = $this->orderService->getActiveCart($customer);
            $this->orderService->addItemToCart($cart, $product->id, 1);

            if (Str::startsWith($buttonId, 'ADD_ITEM_')) {
                $recommendations = $product->crossSellProducts;

                if ($recommendations->isNotEmpty()) {
                    $recommendedProduct = $recommendations->first();
                    $this->sendCrossSellMessage($customer, $product, $recommendedProduct);
                } else {
                    $this->sendConfirmationMessage($customer, $product);
                }
            } else {
                $this->sendConfirmationMessage($customer, $product, true);
            }

        } elseif ($buttonId === 'REJECT_CROSS_SELL') {
            $this->handleCrossSellRejection($customer);

        } elseif (Str::startsWith($buttonId, 'ACTIVATE_STOCK_ALERT_')) {
            $productId = Str::afterLast($buttonId, '_');
            $this->handleStockAlertActivation($customer, $productId);

        } elseif (Str::startsWith($buttonId, 'MORE_DETAILS_')) {
            $productId = Str::afterLast($buttonId, '_');
            $this->sendProductDetailsMessage($customer, $productId);

        } elseif (Str::startsWith($buttonId, 'MORE_IMAGES_')) {
            $productId = Str::afterLast($buttonId, '_');
            $this->sendProductGalleryMessage($customer, $productId);
        } else {
            $this->whatsAppService->sendTextMessage("Desculpe, não reconheci essa opção.", $customer->phone);
        }
    }


    /**
     * Lógica para ativar aviso de estoque.
     */
    protected function handleStockAlertActivation(Customer $customer, string $productId) 
    {
        $product = Product::find($productId);
        if (!$product) return;

        try {
            StockAlert::firstOrCreate(
                ['user_id' => $customer->id, 'product_id' => $productId],
                ['destination' => $customer->phone] 
            );

            $message = "Aviso ativado\n\n";
            $message .= "Obrigado, *{$customer->name}*! Iremos notificá-lo por este número assim que o produto *{$product->name}* voltar ao nosso estoque.";
            $this->whatsAppService->sendTextMessage($message, $customer->phone);
        } catch (Exception $e) {
            if (Str::contains($e->getMessage(), 'Duplicate entry')) {
                $message = "Você já tem um aviso ativo para o produto *{$product->name}*. Iremos notificá-lo.";
            } else {
                Log::error("Erro ao ativar alerta de estoque: " . $e->getMessage());
                $message = "Desculpe, ocorreu um erro ao tentar ativar o aviso de estoque. Por favor, tente novamente mais tarde.";
            }
            $this->whatsAppService->sendTextMessage($message, $customer->phone);
        }
    }

    protected function sendCrossSellMessage(Customer $customer, Product $addedProduct, Product $recProduct)
    {
        $body = "Adicionado *{$addedProduct->name}* ao seu carrinho!\n\n";
        $body .= "Já que está a levar {$addedProduct->name}, que tal completar com *{$recProduct->name}* (Kz " . number_format($recProduct->price, 0, ',', '.') . ")? Esta é a sua chance de levar o kit completo!";

        $buttons = [
            ['id' => 'ADD_CROSS_SELL_' . $recProduct->id, 'title' => '✅ Sim, Adicionar'], // (17 chars) OK
            ['id' => 'REJECT_CROSS_SELL', 'title' => 'Agora Não'], // (9 chars) OK
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Seu pedido está quase completo!", // ENCURTADO
            $recProduct->image_url,
            $buttons
        );
    }

    protected function sendConfirmationMessage(Customer $customer, Product $product, $isCrossSell = false)
    {
        $body = $isCrossSell
            ? "Perfeito! Adicionado *{$product->name}* também! O seu carrinho está a ficar INCRÍVEL!"
            : "✅ *{$product->name}* adicionado ao seu carrinho, *{$customer->name}*! Ótima escolha!";

        $buttons = [
            ['id' => 'FINALIZAR_PEDIDO', 'title' => '🔒 Finalizar e Pagar'], // (19 chars) OK
            ['id' => 'VER_CARRINHO', 'title' => 'Revisar Carrinho'], // (16 chars) OK
        ];
        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Clique em 'Finalizar' para garantir seu pedido!", // ENCURTADO
            null,
            $buttons
        );
    }

    protected function handleCrossSellRejection(Customer $customer)
    {
        $body = "Tudo bem, *{$customer->name}*! Pode continuar a comprar ou avançar para o checkout.";
        $buttons = [
            ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido'], // (16 chars) OK
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'], // (12 chars) OK
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Estamos a guardar o seu pedido!", // ENCURTADO
            null,
            $buttons
        );
    }

    // ===================================================================
    // MÉTODOS DE COMPRA E CHECKOUT 
    // ===================================================================

    private function showCart(Customer $customer)
    {
        $cart = $this->orderService->getActiveCart($customer);

        if ($cart->items->isEmpty()) {
            $this->whatsAppService->sendTextMessage("Seu carrinho está vazio. Vamos adicionar um produto incrível? Basta pesquisar o que procura!", $customer->phone);
            return;
        }

        $message = "Seu Carrinho (Pedido #{$cart->id}):\n\n";

        foreach ($cart->items as $item) {
            $item->load('product');
            $message .= "▪️ *{$item->product->name}* (x{$item->quantity})\n";
            $message .= "    Preço: " . $this->orderService->formatPrice($item->price * $item->quantity) . "\n\n";
        }

        $message .= "*Total: " . $this->orderService->formatPrice($cart->total_price) . "*\n\n";
        $message .= "O total está espetacular! Não espere mais!";

        $buttons = [
            ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido Já'], // (19 chars) OK
            ['id' => 'VER_CARRINHO', 'title' => 'Atualizar Carrinho'] // (18 chars) OK
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $message,
            $customer->phone,
            "Clique no botão para garantir o seu produto!", // ENCURTADO
            null,
            $buttons
        );
    }

    private function finalizeOrder(Customer $customer)
    {
        $cart = $this->orderService->getActiveCart($customer);
        if ($cart->items->isEmpty()) {
            $this->whatsAppService->sendTextMessage("Seu carrinho está vazio. Não pode finalizar.", $customer->phone);
            return;
        }

        if (empty($customer->name)) {
            $msg = "🛑 Antes de finalizar, *{$customer->phone}*, precisamos do seu nome completo.\n\n";
            $msg .= "Por favor, digite o seu nome (ex: 'O meu nome é João').";
            $this->whatsAppService->sendTextMessage($msg, $customer->phone);
            return;
        }
        
        if (empty($cart->uuid)) {
            Log::info("Carrinho {$cart->id} sem UUID. Gerando um novo.");
            $cart->uuid = (string) Str::uuid();
            $cart->save();
        }

        $paymentUrl = route('payment.show', ['uuid' => $cart->uuid]);
        $loginUrl = url('/customer/login');

        $message = "Excelente escolha, *{$customer->name}*! Seu pedido #{$cart->id} está pronto para pagamento.\n\n";
        $message .= "Total: *" . $this->orderService->formatPrice($cart->total_price) . "*\n\n";
        $message .= "Para continuar, informar o endereço e *escolher Tamanhos/Cores* (se aplicável), clique no link ABAIXO para finalizar o pagamento e garantir a entrega:\n{$paymentUrl}\n\n";

        $message .= "========================\n";
        $message .= "*Acompanhamento do Pedido*\n";
        $message .= "Você pode acompanhar o status do seu pedido no nosso portal de cliente:\n";
        $message .= "Link: {$loginUrl}\n";
        $message .= "Usuário: *{$customer->phone}* (Seu número)\n";
        $message .= "Senha: *{$customer->phone}* (Seu número)\n";
        $message .= "========================\n\n";

        $message .= "Assim que recebermos o comprovativo, confirmaremos o pagamento e atualizaremos o status. *Compre agora e receba em tempo recorde!*";

        $this->whatsAppService->sendTextMessage($message, $customer->phone);
    }
    
    private function sendProductCardMessage(Customer $customer, Product $product)
    {
        $defaultStock = Stock::where('is_default', true)->first();
        $stock = $defaultStock ? $product->getStockIn($defaultStock->id) : 0;
        $imageCount = $product->images->count();

        $priceString = "";
        if ($product->promotional_price > 0) {
            $priceString = "💥 **PROMOÇÃO!** Preço: ~" . $this->orderService->formatPrice($product->price) . "~ por **" . $this->orderService->formatPrice($product->promotional_price) . "**!";
        } else {
            $priceString = "Preço: *" . $this->orderService->formatPrice($product->price) . "*";
        }

        $body = "🛍️ *{$product->name}*\n";
        $body .= $product->short_description . "\n\n";
        $body .= $priceString . "\n";
        if ($product->sizes) $body .= "Tamanhos Disponíveis: " . implode(', ', $product->sizes) . "\n";
        if ($product->colors) $body .= "Cores Disponíveis: " . implode(', ', $product->colors) . "\n";

        $buttons = [];

        if ($stock > 0) {
            $body .= "\n✅ *Em estoque! Não perca!*";
            //
            // =================================================================
            //           ⬇️  CORREÇÃO DO BOTÃO AQUI ⬇️
            // =================================================================
            // '🛒 Adicionar ao Pedido' (21+ chars) -> '🛒 Adicionar' (10 chars)
            $buttons[] = ['id' => 'ADD_ITEM_' . $product->id, 'title' => '🛒 Adicionar']; 
            // =================================================================
            //
        } else {
            $body .= "\n🛑 *Sem estoque no momento. Seja avisado!*";
            $buttons[] = ['id' => 'ACTIVATE_STOCK_ALERT_' . $product->id, 'title' => 'Avisar quando chegar']; // (20 chars) OK
        }

        $buttons[] = ['id' => 'MORE_DETAILS_' . $product->id, 'title' => 'Mais Detalhes']; // (14 chars) OK

        if ($imageCount > 0) {
            $buttons[] = ['id' => 'MORE_IMAGES_' . $product->id, 'title' => 'Ver Mais Imagens']; // (16 chars) OK
        }

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Aproveite a oportunidade! Clique numa opção abaixo.", // OK
            $product->image_url,
            $buttons
        );
    }

    private function sendProductDetailsMessage(Customer $customer, string $productId) 
    {
        $product = Product::find($productId);
        if (!$product) return;

        $defaultStock = Stock::where('is_default', true)->first();
        $stock = $defaultStock ? $product->getStockIn($defaultStock->id) : 0;

        $priceString = "";
        if ($product->promotional_price > 0) {
            $priceString = "💥 **PROMOÇÃO!** Preço: ~" . $this->orderService->formatPrice($product->price) . "~ por **" . $this->orderService->formatPrice($product->promotional_price) . "**!";
        } else {
            $priceString = "Preço: *" . $this->orderService->formatPrice($product->price) . "*";
        }

        $body = "🛍️ *{$product->name}*\n\n";
        $body .= "*Descrição Completa:*\n";
        $body .= $product->description . "\n\n"; 
        $body .= "--- Resumo ---\n";
        $body .= $priceString . "\n";
        if ($product->sizes) $body .= "Tamanhos: " . implode(', ', $product->sizes) . "\n";
        if ($product->colors) $body .= "Cores: " . implode(', ', $product->colors) . "\n";

        $buttons = [];

        if ($stock > 0) {
            $body .= "\n✅ *Disponível!*";
            $buttons[] = ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Adicionar ao Pedido']; // (19 chars) OK
        } else {
            $body .= "\n🛑 *Sem estoque no momento. Ative o aviso!*";
            $buttons[] = ['id' => 'ACTIVATE_STOCK_ALERT_' . $product->id, 'title' => 'Avisar quando chegar']; // (20 chars) OK
        }

        $buttons[] = ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido']; // (16 chars) OK
        $buttons[] = ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho']; // (12 chars) OK
        

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Garanta este item fantástico!", // ENCURTADO
            null,
            $buttons
        );
    }

    private function sendProductGalleryMessage(Customer $customer, string $productId) 
    {
        $product = Product::with('images')->find($productId);
        if (!$product) return;

        $this->whatsAppService->sendTextMessage("Aqui estão mais imagens para *{$product->name}*:", $customer->phone);

        foreach ($product->images->sortBy('sort_order') as $image) {
            Log::info('URL das imagens' . $image->url);
            $this->whatsAppService->sendImageMessage($image->url, $customer->phone);
        }

        $buttons = [
            ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Adicionar ao Pedido'], // (19 chars) OK
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'], // (12 chars) OK
        ];

        $this->whatsAppService->sendInteractiveMessage(
            "Gostou das fotos? Deseja adicionar *{$product->name}* ao seu pedido para garantir a sua compra AGORA?",
            $customer->phone,
            "Clique numa opção abaixo para fechar negócio!", // ENCURTADO
            null, 
            $buttons
        );
    }

    // ===================================================================
    // PROMPT DA OPENAI (Mantido idêntico à última versão)
    // ===================================================================

    private function buildOpenAISystemPrompt(): string
    {
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first();
        });
        $categories = Cache::remember('all_categories', 60 * 5, function () {
            return Category::all(['name', 'slug']);
        });
        
        $coupons = Cache::remember('active_coupons', 60 * 5, function () {
            return Coupon::where('is_active', true)->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })->get(['code', 'value', 'type']);
        });

        $paymentMethods = Cache::remember('active_payment_methods', 60 * 5, function () {
            return PaymentMethod::where('is_active', true)->get(['name', 'instructions']);
        });

        $paymentInfo = $paymentMethods->isNotEmpty() 
            ? $paymentMethods->map(fn($pm) => "*{$pm->name}*: {$pm->instructions}")->implode("\n")
            : 'Desculpe, no momento não há métodos de pagamento ativos. Por favor, contacte um operador.';

        $shippingRates = Cache::remember('active_shipping_rates', 60 * 5, function () {
            return ShippingRate::where('is_active', true)->get(['province_name', 'estimated_delivery_time', 'rate']);
        });

        $deliveryLocations = $shippingRates->pluck('province_name')->implode(', ');
        $deliveryTimesAndRates = $shippingRates->map(function($sr) {
            $rate = number_format($sr->rate, 0, ',', '.');
            return "Em *{$sr->province_name}* a entrega custa Kz {$rate} e demora: {$sr->estimated_delivery_time}.";
        })->implode("\n");


        $categoryNames = $categories->pluck('name')->implode(', ');
        $couponInfo = $coupons->isNotEmpty() 
            ? "Temos cupons de desconto ativos! Use o código *{$coupons->first()->code}* para começar a economizar agora. Códigos: " . $coupons->map(fn($c) => "*{$c->code}* (".($c->type === 'percentage' ? "{$c->value}%" : "Kz ".number_format($c->value, 0, ',', '.')).")")->implode(', ') . "."
            : "Não há cupons de desconto ativos no momento. Mas os nossos preços já são fantásticos!";


        if (!$settings) {
            Log::error("ERRO CRÍTICO: Configurações (ID=1) não encontradas.");
            $storeInfo = "- Quem é você?: \"Sou um bot. O meu admin não me configurou.\"";
        } else {
            $productTypesString = $settings->product_types ? implode(', ', $settings->product_types) : 'diversos produtos';
            $storeInfo = "
             - Nome da Loja: \"{$settings->store_name}\"
             - O que é a {$settings->store_name}?: \"{$settings->company_description}\"
             - Tipos de produtos gerais: \"Vendemos principalmente: {$productTypesString}.\"
             - Categorias de produtos disponíveis: \"{$categoryNames}\"
             - Quem é você?: \"{$settings->faq_who_answer}\"
             - Locais de entrega (Disponíveis): \"{$deliveryLocations}\"
             - Detalhes de Entrega (Tempo/Custo): \"{$deliveryTimesAndRates}\"
             - Métodos de pagamento: \"{$paymentInfo}\"
             - Cupons de Desconto: \"{$couponInfo}\"
            ";
        }

        // =================================================================
        //           ⬇️  CORREÇÃO DA IA AQUI ⬇️
        // =================================================================
        // Adicionadas REGRAS DE COERÊNCIA
        // =================================================================
        return <<<PROMPT
Você é um **Assistente de Vendas PRO** da loja virtual. O seu ÚNICO objetivo é **vender** e guiar o cliente ativamente para a compra. Você não é um mero tirador de dúvidas. Use uma linguagem entusiasta e persuasiva.

Sua tarefa é analisar a pergunta do usuário e retornar APENAS um objeto JSON válido.
O seu output DEVE ser um objeto JSON, e nada mais.

---
REGRAS CRÍTICAS DE PERSUASÃO E PESQUISA:

REGRA GERAL DE VENDAS:
- **Seja sempre persuasivo, use linguagem ativa e crie urgência.**
- **Use o nome do cliente no início da saudação, se o nome for conhecido.**
- **Se a pergunta for sobre descontos, ou se for uma busca por produto, MENCIONE SEMPRE OS CUPONS ATIVOS!**
- **Após qualquer FAQ (entrega, pagamento), a resposta deve ser clara e terminar com uma CTA (Call to Action) forte para pesquisa/compra.**

REGRA 1: Busca por NOME (Prioridade Alta)
- Se a pergunta do usuário parecer um NOME, marca ou descrição de produto (ex: "tênis", "camisa", "relógio", "preço do iPhone"), você DEVE fazer uma busca por NOME.
- Use `p.name LIKE '%termo%'` OU `p.short_description LIKE '%termo%'`.
- **NÃO confunda "tênis" com a categoria "Calçados".** "Tênis" é um NOME de produto.

REGRA 2: Busca por CATEGORIA (Prioridade Média)
- Só use esta regra se a REGRA 1 não se aplicar.
- Se a pergunta do usuário for EXATAMENTE o nome de uma categoria (ex: "mostra-me Calçados", "ver Eletrónicos").
- Use `JOIN categories c` e `c.name LIKE '%Categoria%'`.

REGRA 3: FAQ (Prioridade Baixa)
- Se a pergunta for sobre a loja (entrega, pagamento, quem é você, "listar categorias", "cupons", "descontos"), defina "type" como "faq".
- **A resposta (introduction) DEVE ser clara e terminar com uma CTA para pesquisa/compra.** (Ex: "Já que esclareceu a dúvida, que tal ver as nossas *novidades* e aproveitar o desconto?")

REGRA 4: Saudação
- Se for apenas "Olá", "Obrigado", "Bom dia", defina "type" como "greeting".
- **A resposta (introduction) deve ser muito calorosa e imediatamente levar à compra.** (Ex: "Olá! Que bom ter você connosco! Que tipo de produto maravilhoso posso encontrar para si hoje para adicionar ao carrinho?")

---
REGRAS DE COERÊNCIA E SEGURANÇA (MUITO IMPORTANTE):

- **NÃO responda a mensagens que não fazem sentido**, são texto aleatório (ex: "asgfasgf", "texto esquisito"), ou não têm qualquer relação com a loja (ex: "qual é o tempo hoje?", "qual é a capital de Angola?").
- **Se a mensagem do cliente for incoerente, aleatória ou sem sentido, NÃO invente uma resposta de vendas.**
- Nesses casos, a sua resposta JSON DEVE ser:
  `{`
    `"type": "faq",`
    `"introduction": "Desculpe, não entendi. Como posso ajudar com o seu pedido ou com os nossos produtos?",`
    `"sql_query": null`
  `}`
---

INFORMAÇÕES DA LOJA (PARA RESPONDER FAQs):
$storeInfo
---

SCHEMA DO JSON DE SAÍDA:

1. CHAVE "type": "search", "greeting", ou "faq".
2. CHAVE "introduction": 
      - Se "type" for "search", crie uma frase que **incentive a compra e mencione cupons se houver** (ex: "Excelentes escolhas! Encontrei estes produtos que vão direto para o seu carrinho. Lembre-se de usar o seu cupom na finalização!").
      - Se "type" for "greeting", crie uma resposta **calorosa e que incentive a compra**.
      - Se "type" for "faq", a "introduction" DEVE ser a resposta completa, baseada nas INFORMAÇÕES DA LOJA e **terminar com uma CTA para pesquisa/compra** (a menos que seja uma resposta de coerência).
3. CHAVE "sql_query":
    - Se "type" for "search", gere o SQL. O SQL deve ser `SELECT DISTINCT p.id FROM products p`.
    - Sempre `LIMIT 3`.
    - IDs são UUIDs (CHAR(36)).
    - Se "type" for "greeting" OU "faq", defina "sql_query" como `null`.

SCHEMA DB: 
Tabelas: `products`, `categories`, `coupons`, `shipping_rates`, `payment_methods`.
(Lembre-se das regras de JOIN para SQL).

Analise a pergunta do usuário (que virá na próxima mensagem) e retorne APENAS o objeto JSON.
PROMPT;
    }
}