<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use \Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str; // <-- Adicionado
use App\Models\Setting;

class IntelligenceService
{
    protected string $apiKey;
    protected string $model = 'gpt-4o';
    protected string $sqlModel = 'gpt-4o'; // Usar um modelo bom para SQL

    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        if (!$this->apiKey) {
            throw new Exception('Chave da API OpenAI (OPENAI_API_KEY) não configurada.');
        }
    }

    /**
     * ✅ ATUALIZADO: Método privado para centralizar chamadas (agora suporta JSON)
     */
    private function callOpenAI(string $systemPrompt, string $userPrompt, string $model, bool $useJson = false): array
    {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.1, // Manter a IA focada
        ];

        // Adiciona o modo JSON se solicitado
        if ($useJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $response = Http::withToken($this->apiKey)
            ->timeout(120)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            Log::error("Erro da API OpenAI ({$model}): " . $response->body());
            throw new Exception("Falha na API OpenAI (HTTP status: {$response->status()})");
        }

        return $response->json();
    }

    /**
     * Função 1: Gera a consulta SQL (Agora usa o callOpenAI)
     * (Vamos manter esta como estava, a funcionar bem com a Análise Inteligente)
     */
    public function generateQueryFromPrompt(string $prompt): string
    {
        $systemPrompt = $this->buildSystemPrompt();

        // O modelo gpt-4o é bom o suficiente e entende JSON
        $jsonResponse = $this->callOpenAI($systemPrompt, $prompt, $this->sqlModel, true);

        // O 'content' é uma string JSON, ex: "{\"sql_query\": \"SELECT ...\"}"
        $content = $jsonResponse['choices'][0]['message']['content'];
        $data = json_decode($content, true);

        if (is_null($data) || empty($data['sql_query'])) {
            Log::error("OpenAI retornou JSON inválido ou query vazia: " . $content);
            throw new Exception("A IA retornou uma resposta inválida. Resposta: " . $content);
        }

        Log::info("IA Gerou SQL: " . $data['sql_query']);
        return $data['sql_query'];
    }

    /**
     * Função 2: Gera análise de negócio a partir dos dados (Agora usa o callOpenAI)
     */
    public function getAnalysisForData(array $data, string $promptTitle): string
    {
        $systemPrompt = $this->buildAnalysisPrompt();

        // Limitar dados enviados para a IA
        $dataSample = array_slice($data, 0, 50);
        $dataJson = json_encode($dataSample);

        $userPrompt = "Analise os seguintes dados com base no meu pedido original.\n\n";
        $userPrompt .= "O MEU PEDIDO ORIGINAL FOI: \"{$promptTitle}\"\n\n";
        $userPrompt .= "OS DADOS RESULTANTES (JSON) SÃO:\n{$dataJson}";

        $response = $this->callOpenAI($systemPrompt, $userPrompt, $this->model, false);

        return $response['choices'][0]['message']['content'];
    }

    /**
     * Função 3: Gera Insights de Negócio Proativos
     */
    public function generateBusinessInsights(): string
    {
        $kpiData = $this->getKpiData(); // (Lógica de busca de KPIs)
        $dataString = json_encode($kpiData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $systemPrompt = $this->buildAnalysisPrompt();
        $userPrompt = "
        Hoje é " . Carbon::now()->isoFormat('LLLL') . ".
        Eu não fiz uma pergunta específica. O meu pedido é que você atue como um Analista de Negócios Proativo.
        Analise os seguintes dados de KPI (Key Performance Indicators) da minha loja e gere um relatório de insights.

        Os dados são (em JSON):
        {$dataString}

        O seu relatório deve focar-se em:
        1.  **Vendas:** Como estamos esta semana em comparação com a semana passada?
        2.  **Produtos:** Quais produtos estão a gerar mais receita?
        3.  **Clientes:** Quem são os clientes mais valiosos?
        4.  **Riscos:** Há algum risco imediato (como stock baixo) que eu deva saber?

        Seja direto, profissional e dê conselhos acionáveis.
        ";

        $response = $this->callOpenAI($systemPrompt, $userPrompt, $this->model, false);
        return $response['choices'][0]['message']['content'];
    }

    /**
     * ✅✅✅ NOVA FUNÇÃO 4: Gera Previsão de Vendas
     */
    public function generateSalesForecast(): array
    {
        // 1. Obter dados históricos (Vendas mensais dos últimos 24 meses)
        $historicalSales = DB::select("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as mes, SUM(total_price) as total_vendas
            FROM carts
            WHERE status = 'paid' AND created_at >= ?
            GROUP BY mes
            ORDER BY mes ASC
        ", [Carbon::now()->subMonths(24)]);

        // 2. Criar Prompts para a IA
        $dataString = json_encode($historicalSales);

        $systemPrompt = "
        Você é um Data Scientist e analista financeiro. A sua tarefa é analisar dados históricos de vendas e criar uma previsão para os próximos 6 meses.
        A moeda é o Kwanza Angolano (Kz).

        REGRAS OBRIGATÓRIAS:
        1.  Analise os dados históricos de vendas (em JSON) fornecidos.
        2.  Crie uma previsão de vendas mensal para os PRÓXIMOS 6 MESES.
        3.  Crie uma breve análise (em Markdown) sobre a tendência (crescimento, queda, sazonalidade) e a previsão. Formate valores como '100.000 Kz'.
        4.  A sua resposta DEVE SER UM ÚNICO OBJETO JSON VÁLIDO.
        5.  O JSON deve ter EXATAMENTE a seguinte estrutura:
            {
              \"historical\": [{\"mes\": \"YYYY-MM\", \"total_vendas\": 12345}, ...],
              \"forecast\": [{\"mes\": \"YYYY-MM\", \"total_vendas\": 67890}, ...],
              \"analysis\": \"...texto da análise em markdown...\"
            }
        
        NÃO inclua NENHUM outro texto fora deste objeto JSON.
        ";

        $userPrompt = "
        Dados históricos de vendas:
        {$dataString}

        Por favor, gere a previsão e a análise no formato JSON solicitado.
        Os meses previstos devem seguir a sequência (ex: se o último dado é 2025-10, o primeiro previsto deve ser 2025-11).
        ";

        // 3. Chamar a IA em modo JSON
        $response = $this->callOpenAI($systemPrompt, $userPrompt, $this->model, true);

        // 4. Decodificar e retornar
        $content = $response['choices'][0]['message']['content'];
        $decodedData = json_decode($content, true);

        if (is_null($decodedData) || !isset($decodedData['historical']) || !isset($decodedData['forecast']) || !isset($decodedData['analysis'])) {
            Log::error("IA retornou JSON inválido para previsão: " . $content);
            throw new Exception("A IA retornou um formato de dados de previsão inválido.");
        }

        return $decodedData;
    }


    // --- FUNÇÕES HELPER (Auxiliares) ---

    /**
     * ✅ NOVO HELPER: Validação de Segurança
     */
    public function isSafeQuery(string $sql): bool
    {
        $trimmedSql = strtoupper(trim($sql, " \t\n\r\0\x0B;()"));
        if (!Str::startsWith($trimmedSql, 'SELECT')) return false;

        $forbiddenKeywords = [
            'DELETE',
            'UPDATE',
            'INSERT',
            'DROP',
            'ALTER',
            'TRUNCATE',
            'CREATE',
            'REPLACE',
            'GRANT',
            'REVOKE',
            'SET',
            'LOCK',
            'UNLOCK',
            'CALL'
        ];

        foreach ($forbiddenKeywords as $keyword) {
            if (preg_match("/\b{$keyword}\b/i", $sql)) {
                return false;
            }
        }
        return true;
    }

    private function getKpiData(): array
    {
        $kpiData = [];
        try {
            // --- KPI 1: Vendas Semanal (Últimos 7 dias vs 7 dias anteriores) ---
            $salesComparison = DB::selectOne("
                SELECT
                  SUM(CASE WHEN created_at >= ? THEN total_price ELSE 0 END) AS 'vendas_ultimos_7_dias',
                  SUM(CASE WHEN created_at >= ? AND created_at < ? THEN total_price ELSE 0 END) AS 'vendas_7_dias_anteriores'
                FROM carts
                WHERE status = 'paid' AND created_at >= ?
            ", [
                Carbon::now()->subDays(7),
                Carbon::now()->subDays(14),
                Carbon::now()->subDays(7),
                Carbon::now()->subDays(14)
            ]);
            $kpiData['comparacao_vendas_semanal'] = (array) $salesComparison;

            // --- KPI 2: Top 5 Produtos (Últimos 30 dias) ---
            $kpiData['top_5_produtos_30d'] = DB::select("
                SELECT p.name, SUM(ci.quantity * ci.price) as 'total_vendido'
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                JOIN carts c ON ci.cart_id = c.id
                WHERE c.status = 'paid' AND c.created_at >= ?
                GROUP BY p.id, p.name
                ORDER BY total_vendido DESC
                LIMIT 5
            ", [Carbon::now()->subDays(30)]);

            // --- KPI 3: Top 5 Clientes (Últimos 30 dias) ---
            // Usa customer_id, que é UUID
           $kpiData['top_5_clientes_30d'] = DB::select("
                SELECT cu.name, cu.phone, SUM(c.total_price) as 'total_gasto'
                FROM carts c
                JOIN users cu ON c.user_id = cu.id  -- 1. Junção correta (user_id)
                WHERE c.status = 'paid' 
                  AND c.created_at >= ?
                  AND cu.role = 'customer'          -- 2. Filtro de Role
                GROUP BY cu.id, cu.name, cu.phone
                ORDER BY total_gasto DESC
                LIMIT 5
            ", [Carbon::now()->subDays(30)]);

            // --- KPI 4: Produtos com Stock Baixo (Stock < 10) ---
            // Soma os movimentos para obter o stock atual
            $kpiData['stock_baixo'] = DB::select("
                SELECT p.name, SUM(sm.quantity) as 'stock_atual'
                FROM products p
                JOIN stock_movements sm ON p.id = sm.product_id
                GROUP BY p.id, p.name
                HAVING stock_atual < 10
                ORDER BY stock_atual ASC
                LIMIT 10
            ");
        } catch (\Exception $e) {
            // Este bloco catch é acionado quando o SQL falha
            $errorMessage = "Falha crítica na Base de Dados ao buscar KPIs: " . $e->getMessage();
            \Log::error("Erro ao buscar KPIs para IA: " . $errorMessage);

            // Retorna um erro formatado para a IA analisar
            return [
                'erro_critico' => $errorMessage,
                'data_unavailable' => true
            ];
        }
        return $kpiData;
    }

    private function buildAnalysisPrompt(): string
    {
        // (O seu prompt de Análise, com o contexto de Kz, sem alterações)
        return <<<PROMPT
Você é um Analista de Negócios (Business Analyst) de topo, a preparar um relatório para um gestor em Angola.
Sua resposta deve ser em Português (PT-AO).
A sua resposta DEVE estar em formato Markdown (use títulos, negrito e listas).
---
CONTEXTO DE NEGÓCIO E MOEDA (MUITO IMPORTANTE):
1.  A empresa está localizada em Angola.
2.  A moeda é o Kwanza Angolano (símbolo: Kz).
3.  NUNCA use o símbolo '$' (Dólar).
4.  Ao formatar valores, use pontos como separador de milhares e o sufixo ' Kz' (ex: 7.798 Kz, 50.000 Kz, 1.234.567 Kz).
---
Eu vou fornecer-lhe dados. O seu trabalho é analisar os dados e escrever:
1.  Um `**Resumo Executivo**` (2-3 frases).
2.  Uma lista de `**Insights Acionáveis**` (3 a 5 pontos).
Seja direto, profissional e foque-se no que os dados revelam.
PROMPT;
    }

    private function buildSystemPrompt(): string
    {
        // (O seu prompt de SQL, sem alterações)
        $schema = $this->getDynamicSchema();
        $dbDialect = DB::connection()->getDriverName();
        $currentDate = Carbon::now()->toDateString();

        return <<<PROMPT
Você é um assistente de Business Intelligence (BI) e especialista em SQL.
Sua tarefa é converter um prompt em linguagem natural do usuário numa consulta SQL (dialecto {$dbDialect}) ÚNICA e SEGURA (apenas SELECT).
REGRAS OBRIGATÓRIAS:
1.  **APENAS JSON:** A sua resposta DEVE ser um objeto JSON no formato: { "sql_query": "SELECT ...;" }
2.  **APENAS SELECT:** NUNCA gere queries com UPDATE, DELETE, DROP, INSERT, etc.
3.  **USE O SCHEMA:** Use o schema de tabelas abaixo para fazer os JOINs corretos.
4.  **STATUS DE PEDIDOS (Tabela 'carts'):** O campo 'status' aceita os valores: 'active', 'paid', 'submitted', 'cancelled'.
5.  **VENDAS:** Para Faturação/Vendas, a consulta DEVE filtrar por `carts.status = 'paid'`.
6.  **DATAS:** Hoje é: {$currentDate}.
SCHEMA DA BASE DE DADOS:
{$schema}
PROMPT;
    }

    private function getDynamicSchema(): string
    {
        // (O seu código de getDynamicSchema() permanece o mesmo)
        return Cache::remember('ai_db_schema', now()->addHours(24), function () {
            $allowedTables = [
                'products',
                'categories',
                'category_product',
                'product_images',
                'product_cross_sell',
                'users',
                'carts',
                'cart_items',
                'suppliers',
                'stocks',
                'purchases',
                'purchase_items',
                'stock_movements',
                'saved_reports',
                'expenses'
            ];
            $schemaString = "";
            foreach ($allowedTables as $tableName) {
                if (!Schema::hasTable($tableName)) continue;
                $columns = Schema::getColumnListing($tableName);
                $columnDetails = [];
                foreach ($columns as $column) {
                    try {
                        $type = Schema::getColumnType($tableName, $column);
                        $columnDetails[] = "{$column} ({$type})";
                    } catch (Exception $e) { /*...*/
                    }
                }
                $schemaString .= "- Tabela '{$tableName}' (" . implode(', ', $columnDetails) . ")\n";
            }
            return $schemaString;
        });
    }

    public function generateCrossSellAnalysis(): string
    {
        // 1. Obter os dados brutos de "comprados juntos" dos últimos 90 dias
        // Esta query encontra pares de produtos que estiveram nos mesmos carrinhos pagos
        $crossSellData = DB::select("
            SELECT
                p1.name as produto_A,
                p2.name as produto_B,
                COUNT(DISTINCT c.id) as contagem_pedidos
            FROM cart_items ci1
            JOIN cart_items ci2 ON ci1.cart_id = ci2.cart_id AND ci1.product_id != ci2.product_id
            JOIN products p1 ON ci1.product_id = p1.id
            JOIN products p2 ON ci2.product_id = p2.id
            JOIN carts c ON ci1.cart_id = c.id
            WHERE
                c.status = 'paid'
                AND c.created_at >= ?
                AND ci1.product_id < ci2.product_id -- Evita duplicados (A-B e B-A)
            GROUP BY p1.name, p2.name
            ORDER BY contagem_pedidos DESC
            LIMIT 15 -- Limita aos 15 pares mais populares
        ", [Carbon::now()->subDays(90)]);

        if (empty($crossSellData)) {
            return "Não foram encontrados dados suficientes de 'comprados juntos' nos últimos 90 dias para fazer uma análise de cross-sell.";
        }

        // 2. Criar os Prompts para a IA
        $dataString = json_encode($crossSellData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Reutilizar o prompt de análise de Kz
        $systemPrompt = $this->buildAnalysisPrompt();

        $userPrompt = "
        Você é um especialista em marketing e e-commerce.
        Eu rodei uma query que me deu os 15 pares de produtos que são mais frequentemente comprados juntos em pedidos pagos.

        Os dados são (em JSON):
        {$dataString}

        O seu trabalho é analisar estes dados e gerar um relatório de marketing:
        1.  Um `**Resumo Executivo**` sobre os padrões de compra que você detetou.
        2.  Uma lista de `**Sugestões de Cross-Sell e Upsell**`.
        
        Seja muito específico. Em vez de dizer 'Crie um bundle', diga:
        -   **Criar Bundle:** 'Clientes compram 'Produto A' e 'Produto B' juntos 10 vezes. Crie um \"Kit Essencial\" com 5% de desconto.'
        -   **Recomendação (Upsell):** 'Se o cliente adicionar 'Produto A' ao carrinho, mostre o 'Produto B' como \"Frequentemente comprado junto\".'
        
        Lembre-se do contexto de Angola (Kz).
        ";

        // 3. Chamar a IA
        $response = $this->callOpenAI($systemPrompt, $userPrompt, $this->model, false);

        return $response['choices'][0]['message']['content'];
    }

    public function runChatQuery(string $prompt): array
    {
        $sqlQuery = "";
        $data = [];
        $analysis = "";
        $isSafe = false;

        try {
            // 1. Gerar o SQL
            // Usamos o prompt de SQL que retorna JSON {"sql_query": "..."}
            $systemPrompt = $this->buildSystemPrompt();
            $jsonResponse = $this->callOpenAI($systemPrompt, $prompt, $this->sqlModel, true);
            $content = $jsonResponse['choices'][0]['message']['content'];
            $parsedData = json_decode($content, true);

            if (is_null($parsedData) || empty($parsedData['sql_query'])) {
                Log::error("IA retornou SQL JSON inválido: " . $content);
                throw new Exception("A IA retornou um SQL inválido. Tente reformular a pergunta.");
            }
            $sqlQuery = $parsedData['sql_query'];

            // 2. Validar o SQL
            if (!$this->isSafeQuery($sqlQuery)) {
                throw new Exception("A IA gerou uma consulta insegura (ex: UPDATE, DELETE) e foi bloqueada.");
            }
            $isSafe = true;

            // 3. Executar o SQL
            $data = DB::select($sqlQuery);
            $data = json_decode(json_encode($data), true); // Converte para array

            // 4. Se não houver dados, responda
            if (empty($data)) {
                return [
                    'analysis'    => "A sua pergunta foi processada (`{$sqlQuery}`), mas não retornou nenhum dado.",
                    'data'        => [],
                    'columns'     => [],
                    'sql_query'   => $sqlQuery
                ];
            }

            // 5. Gerar Análise dos dados (Enviar os dados de volta para a IA)
            // Usamos o prompt de análise de Kz
            $analysisPrompt = $this->buildAnalysisPrompt();

            // Limita os dados enviados para a IA para 50 linhas
            $dataSample = array_slice($data, 0, 50);

            $userPrompt = "Analise os seguintes dados com base no meu pedido original e responda-me diretamente (em Português de Angola).\n\n";
            $userPrompt .= "O MEU PEDIDO ORIGINAL FOI: \"{$prompt}\"\n\n";
            $userPrompt .= "OS DADOS (JSON) SÃO:\n" . json_encode($dataSample);
            $userPrompt .= "\n\nResponda diretamente à pergunta original, usando estes dados.";

            $response = $this->callOpenAI($analysisPrompt, $userPrompt, $this->model, false);
            $analysis = $response['choices'][0]['message']['content'];

            return [
                'analysis'    => $analysis,
                'data'        => $data, // Retorna os dados completos
                'columns'     => array_keys($data[0]),
                'sql_query'   => $sqlQuery
            ];
        } catch (Exception $e) {
            Log::error("Erro em runChatQuery: " . $e->getMessage());

            // Retorna uma mensagem de erro amigável
            $errorMsg = "Ocorreu um erro ao processar o seu pedido: " . $e->getMessage();
            if ($isSafe && !empty($sqlQuery)) {
                $errorMsg = "A IA gerou um SQL (`{$sqlQuery}`) que falhou ao ser executado. Erro: " . $e->getMessage();
            }

            return [
                'analysis'    => $errorMsg,
                'data'        => [],
                'columns'     => [],
                'sql_query'   => $sqlQuery
            ];
        }
    }

    public function getAdminRecipients(): array
    {
        $settings = Cache::remember('shop_settings', now()->addMinutes(60), function () {
            return Setting::first();
        });

        if (!$settings || empty($settings->support_email)) {
            \Log::warning('Configuração de support_email não encontrada no Model Setting.');
            return [];
        }

        $emailsString = $settings->support_email;

        $emails = collect(preg_split('/[;,]/', $emailsString))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->toArray();

        return array_unique($emails);
    }
}
