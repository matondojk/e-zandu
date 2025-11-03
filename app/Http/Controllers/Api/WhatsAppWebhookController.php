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

// Imports de Suporte
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessWhatsAppMessage; // ✅ IMPORTAR O JOB

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
     * ✅ CORRIGIDO: Agora apenas dispara um Job (Fila) para ser RÁPIDO.
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
            // Isto permite-nos responder 'OK' imediatamente, evitando o loop de retentativas do WhatsApp.
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
    // ✅ MODIFICADO: Renomeado para 'processTextMessage' e agora é 'public'
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


        // O resto desta função (lógica de 'type' == 'search', etc.) permanece IDÊNTICA
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
                // ✅ CORREÇÃO: Usar 'whereIn' com UUIDs
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
                $this->whatsAppService->sendTextMessage($data['introduction'], $userPhone);
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
    // ✅ MODIFICADO: Agora é 'public' para o Job
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
    protected function handleStockAlertActivation(Customer $customer, string $productId) // ✅ ID é string
    {
        $product = Product::find($productId);
        if (!$product) return;

        try {
            StockAlert::firstOrCreate(
                ['customer_id' => $customer->id, 'product_id' => $productId],
                ['destination' => $customer->phone] 
            );

            $message = "🔔 Aviso ativado! 🔔\n\n";
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
        $body .= "Já que está a levar {$addedProduct->name}, que tal completar com *{$recProduct->name}* (Kz " . number_format($recProduct->price, 0, ',', '.') . ")?";

        $buttons = [
            ['id' => 'ADD_CROSS_SELL_' . $recProduct->id, 'title' => 'Sim, Adicionar'],
            ['id' => 'REJECT_CROSS_SELL', 'title' => 'Agora Não'],
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Pode ver o seu carrinho a qualquer momento.",
            $recProduct->image_url,
            $buttons
        );
    }

    protected function sendConfirmationMessage(Customer $customer, Product $product, $isCrossSell = false)
    {
        $body = $isCrossSell
            ? "Perfeito! Adicionado *{$product->name}* também!"
            : "✅ *{$product->name}* adicionado ao seu carrinho!";

        $buttons = [
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'], 
            ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido'], 
        ];
        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Clique abaixo para continuar.",
            null,
            $buttons
        );
    }

    protected function handleCrossSellRejection(Customer $customer)
    {
        $body = "Tudo bem! Pode continuar a comprar ou avançar para o checkout.";
        $buttons = [
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'], 
            ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido'], 
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Clique numa das opções.",
            null,
            $buttons
        );
    }

    // ===================================================================
    // MÉTODOS DE COMPRA E CHECKOUT (Sem alterações)
    // ===================================================================

    private function showCart(Customer $customer)
    {
        $cart = $this->orderService->getActiveCart($customer);

        if ($cart->items->isEmpty()) {
            $this->whatsAppService->sendTextMessage("Seu carrinho está vazio.", $customer->phone);
            return;
        }

        $message = "Seu Carrinho (Pedido #{$cart->id}):\n\n";

        foreach ($cart->items as $item) {
            $item->load('product');
            $message .= "▪️ *{$item->product->name}* (x{$item->quantity})\n";
            $message .= "   Preço: " . $this->orderService->formatPrice($item->price * $item->quantity) . "\n\n";
        }

        $message .= "*Total: " . $this->orderService->formatPrice($cart->total_price) . "*";

        $buttons = [
            ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido'], 
            ['id' => 'VER_CARRINHO', 'title' => 'Atualizar Carrinho'] 
        ];

        $this->whatsAppService->sendInteractiveMessage(
            $message,
            $customer->phone,
            "Clique no botão para continuar.",
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
            $msg = "🛑 Antes de finalizar, precisamos do seu nome completo.\n\n";
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
        $message .= "Para continuar, informar o endereço e *escolher Tamanhos/Cores* (se aplicável), clique no link: {$paymentUrl}\n\n";

        $message .= "========================\n";
        $message .= "*Acompanhamento do Pedido*\n";
        $message .= "Você pode acompanhar o status do seu pedido no nosso portal de cliente:\n";
        $message .= "Link: {$loginUrl}\n";
        $message .= "Usuário: *{$customer->phone}* (Seu número)\n";
        $message .= "Senha: *{$customer->phone}* (Seu número)\n";
        $message .= "========================\n\n";

        $message .= "Assim que recebermos o comprovativo, confirmaremos o pagamento e atualizaremos o status.";

        $this->whatsAppService->sendTextMessage($message, $customer->phone);
    }
    
    private function sendProductCardMessage(Customer $customer, Product $product)
    {
        $defaultStock = Stock::where('is_default', true)->first();
        $stock = $defaultStock ? $product->getStockIn($defaultStock->id) : 0;
        $imageCount = $product->images->count();

        $priceString = "";
        if ($product->promotional_price > 0) {
            $priceString = "Preço: ~" . $this->orderService->formatPrice($product->price) . "~ *" . $this->orderService->formatPrice($product->promotional_price) . "* (Promoção!)";
        } else {
            $priceString = "Preço: *" . $this->orderService->formatPrice($product->price) . "*";
        }

        $body = "🛍️ *{$product->name}*\n";
        $body .= $product->short_description . "\n\n";
        $body .= $priceString . "\n";
        if ($product->sizes) $body .= "Tamanhos: " . implode(', ', $product->sizes) . "\n";
        if ($product->colors) $body .= "Cores: " . implode(', ', $product->colors) . "\n";

        $buttons = [];

        if ($stock > 0) {
            $buttons[] = ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Adicionar ao Pedido'];
        } else {
            $body .= "\n🛑 *Sem estoque no momento.*\n";
            $buttons[] = ['id' => 'ACTIVATE_STOCK_ALERT_' . $product->id, 'title' => 'Avisar quando chegar'];
        }

        $buttons[] = ['id' => 'MORE_DETAILS_' . $product->id, 'title' => 'Mais Detalhes'];

        if ($imageCount > 0) {
            $buttons[] = ['id' => 'MORE_IMAGES_' . $product->id, 'title' => 'Ver Mais Imagens'];
        }

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Clique numa opção abaixo",
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
            $priceString = "Preço: ~" . $this->orderService->formatPrice($product->price) . "~ *" . $this->orderService->formatPrice($product->promotional_price) . "* (Promoção!)";
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
            $buttons[] = ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Adicionar ao Pedido'];
        } else {
            $body .= "\n🛑 *Sem estoque no momento.*\n";
            $buttons[] = ['id' => 'ACTIVATE_STOCK_ALERT_' . $product->id, 'title' => 'Avisar quando chegar'];
        }

        $buttons[] = ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'];
        $buttons[] = ['id' => 'FINALIZAR_PEDIDO', 'title' => 'Finalizar Pedido'];

        $this->whatsAppService->sendInteractiveMessage(
            $body,
            $customer->phone,
            "Continue a sua compra abaixo",
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
            ['id' => 'ADD_ITEM_' . $product->id, 'title' => 'Adicionar ao Pedido'],
            ['id' => 'VER_CARRINHO', 'title' => 'Ver Carrinho'],
        ];

        $this->whatsAppService->sendInteractiveMessage(
            "Gostou das fotos? Deseja adicionar *{$product->name}* ao seu pedido?",
            $customer->phone,
            "Clique numa opção abaixo:",
            null, 
            $buttons
        );
    }

    // ===================================================================
    // PROMPT DA OPENAI (✅ CORRIGIDO E MAIS INTELIGENTE)
    // ===================================================================

    private function buildOpenAISystemPrompt(): string
    {
        $settings = Cache::remember('shop_settings', 60 * 5, function () {
            return Setting::first();
        });
        $categories = Cache::remember('all_categories', 60 * 5, function () {
            return Category::all(['name', 'slug']);
        });
        $categoryNames = $categories->pluck('name')->implode(', ');

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
             - Locais de entrega?: \"{$settings->faq_locations_answer}\"
             - Tempo de entrega?: \"{$settings->faq_time_answer}\"
             - Métodos de pagamento?: \"{$settings->faq_payment_answer}\"
            ";
        }

        // ✅ PROMPT ATUALIZADO PARA SER MAIS EXPLÍCITO
        return <<<PROMPT
Você é um assistente de loja virtual. Sua tarefa é analisar a pergunta do usuário e retornar APENAS um objeto JSON válido.
O seu output DEVE ser um objeto JSON, e nada mais.

---
REGRAS CRÍTICAS DE PESQUISA (SIGA ESTA ORDEM):

REGRA 1: Busca por NOME (Prioridade Alta)
- Se a pergunta do usuário parecer um NOME de produto (ex: "tênis", "camisa", "relógio", "preço do iPhone"), você DEVE fazer uma busca por NOME.
- Use `p.name LIKE '%termo%'` OU `p.short_description LIKE '%termo%'`.
- NÃO confunda "tênis" com a categoria "Calçados". "Tênis" é um NOME de produto.

REGRA 2: Busca por CATEGORIA (Prioridade Média)
- Só use esta regra se a REGRA 1 não se aplicar.
- Se a pergunta do usuário for EXATAMENTE o nome de uma categoria (ex: "mostra-me Calçados", "ver Eletrónicos", "Categoria Roupas") OU se o usuário pedir "quais produtos da categoria X".
- Use `JOIN categories c` e `c.name LIKE '%Categoria%'`.

REGRA 3: FAQ (Prioridade Baixa)
- Se a pergunta for sobre a loja (entrega, pagamento, quem é você, "listar categorias", "quais categorias existem"), defina "type" como "faq".

REGRA 4: Saudação
- Se for apenas "Olá", "Obrigado", "Bom dia", defina "type" como "greeting".
---

INFORMAÇÕES DA LOJA (PARA RESPONDER FAQs):
$storeInfo
---

SCHEMA DO JSON DE SAÍDA:

1. CHAVE "type": "search", "greeting", ou "faq".
2. CHAVE "introduction": 
   - Se "type" for "search", crie uma frase amigável (ex: "Claro! Aqui estão os tênis que encontrei:").
   - Se "type" for "greeting", crie uma resposta amigável (ex: "Olá! Posso ajudar a encontrar produtos em nossa loja?").
   - Se "type" for "faq", a "introduction" DEVE ser a resposta completa, baseada nas INFORMAÇÕES DA LOJA.
3. CHAVE "sql_query":
   - Se "type" for "search", gere o SQL. O SQL deve ser `SELECT DISTINCT p.id FROM products p`.
   - Sempre `LIMIT 3`.
   - IDs são UUIDs (CHAR(36)).
   - Se "type" for "greeting" OU "faq", defina "sql_query" como `null`.

SCHEMA DB: 
Tabelas: `products` (id (uuid), name, short_description, ...), `categories` (id (uuid), name).
Pivot: `category_product` (product_id (uuid), category_id (uuid)).

---
EXEMPLOS DE DECISÃO:

EXEMPLO 1 (Busca por CATEGORIA - REGRA 2):
PERGUNTA: "Quero ver os produtos de Calçados"
JSON ESPERADO:
{
  "type": "search",
  "introduction": "Claro! Aqui estão os produtos da categoria Calçados:",
  "sql_query": "SELECT DISTINCT p.id FROM products p JOIN category_product cp ON p.id = cp.product_id JOIN categories c ON cp.category_id = c.id WHERE c.name LIKE '%Calçados%' LIMIT 3"
}

EXEMPLO 2 (Busca por NOME - REGRA 1):
PERGUNTA: "Quais os tênis mais baratos?"
JSON ESPERADO:
{
  "type": "search",
  "introduction": "Claro! Aqui estão os tênis mais baratos que encontrei:",
  "sql_query": "SELECT DISTINCT p.id FROM products p WHERE (p.name LIKE '%tênis%' OR p.short_description LIKE '%tênis%' OR p.description LIKE '%tênis%') ORDER BY p.price ASC LIMIT 3"
}

EXEMPLO 3 (FAQ - REGRA 3):
PERGUNTA: "Quais são as categorias que vendem?"
JSON ESPERADO:
{
  "type": "faq",
  "introduction": "As nossas categorias principais são: $categoryNames",
  "sql_query": null
}

EXEMPLO 4 (Busca por NOME - REGRA 1) <- O SEU CASO
PERGUNTA: "quero comprar um ténis."
JSON ESPERADO:
{
  "type": "search",
  "introduction": "Claro! Aqui estão os tênis que encontrei:",
  "sql_query": "SELECT DISTINCT p.id FROM products p WHERE (p.name LIKE '%tênis%' OR p.short_description LIKE '%tênis%' OR p.description LIKE '%tênis%') LIMIT 3"
}

Analise a pergunta do usuário (que virá na próxima mensagem) e retorne APENAS o objeto JSON.
PROMPT;
    }
}