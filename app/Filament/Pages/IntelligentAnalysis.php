<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Services\IntelligenceService;
use \Exception;
use PDF;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action;
use App\Models\SavedReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class IntelligentAnalysis extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static string $view = 'filament.pages.intelligent-analysis';
    protected static ?string $navigationLabel = 'Análise Inteligente';
    protected static ?string $title = 'Módulo de Análise Inteligente';
    protected static ?int $navigationSort = -1;
    public ?string $chartDisplayLabel = null;
    // --- ✅ NOVAS PROPRIEDADES ---
    public bool $isGenerating = false; // Propriedade que faltava para o loading
    public ?string $chartLabelColumn = null; // A coluna de texto (ex: 'name')
    public ?string $chartDataColumn = null; // A coluna de número (ex: 'total_sales')

    // --- Estado do Formulário (Mantido) ---
    public ?array $formData = [
        'prompt' => '',
        'suggestions' => null,
        'load_report' => null,
        'start_date' => null,
        'end_date' => null,
        'output_mode' => 'report',
        'chart_type' => 'bar',
    ];
    public array $savedReportOptions = [];

    // --- Estado dos Resultados (Mantido) ---
    public ?string $reportId = null;
    public ?string $reportTitle = null;
    public ?array $reportData = null;
    public ?array $reportColumns = null;
    public bool $chartsDisabled = false;
    public ?array $forecastData = null; // Armazena a resposta JSON completa da IA
    public ?string $forecastAnalysis = null; // Armazena apenas o texto de análise
    public bool $forecastLoading = false;
    // --- ✅ GESTÃO DAS ABAS (TABS) ---
    public string $mainView = 'report'; // 'report' ou 'analysis'
    public ?string $analysisResult = null;
    public bool $analysisLoading = false;

    // Controlo da visualização (dentro da aba 'report')
    public string $currentMode = 'report'; // 'report' ou 'chart'
    public string $currentType = 'visualization'; // 'visualization', 'bar', etc

    // --- REMOVIDO ---
    // public bool $showAnalysisModal = false; // Não precisamos mais de modal

    protected IntelligenceService $ai;

    public function boot(IntelligenceService $ai): void
    {
        $this->ai = $ai;
    }

    public function mount(): void
    {
        $this->loadSavedReports();
    }

    // Helper para as sugestões (para manter o form() limpo)
    private function getPromptSuggestions(): array
    {
        return [
            // (As suas sugestões)
            'SELECT p.name AS product_name, SUM(ci.price * ci.quantity) AS total_sales FROM products p JOIN cart_items ci ON p.id = ci.product_id JOIN carts c ON ci.cart_id = c.id WHERE c.status = \'paid\' GROUP BY p.name ORDER BY total_sales DESC LIMIT 5' => 'Top 5 Produtos Mais Vendidos (Valor)',
            // ...
        ];
    }

    private function translateChartLabel(string $columnName): string
    {
        $translations = [
            'total_sales' => 'Vendas Totais (Kz)',
            'total_price' => 'Valor Total (Kz)',
            'total_quantity' => 'Quantidade Total',
            'total_profit' => 'Lucro Total (Kz)',
            'name' => 'Nome',
            'product_name' => 'Produto',
            'category_name' => 'Categoria',
            'supplier_name' => 'Fornecedor',
            'mes' => 'Mês', 
            // Adicione mais conforme necessário
        ];

        // Tenta tradução exata (case-insensitive)
        $lowerColumn = strtolower($columnName);
        if (isset($translations[$lowerColumn])) {
            return $translations[$lowerColumn];
        }
        
        // Remove underscores e capitaliza (Fallback)
        $cleanName = str_replace('_', ' ', $columnName);
        return Str::title($cleanName);
    }

    // --- Definição do Formulário (Mantido 100% igual) ---
    public function form(Form $form): Form
    {
        $promptSuggestions = [
            'SELECT p.name AS product_name, SUM(ci.price * ci.quantity) AS total_sales FROM products p JOIN cart_items ci ON p.id = ci.product_id JOIN carts c ON ci.cart_id = c.id WHERE c.status = \'paid\' GROUP BY p.name ORDER BY total_sales DESC LIMIT 5' => 'Top 5 Produtos Mais Vendidos (Valor)',
            'SELECT c.name AS customer_name, SUM(ca.total_price) AS total_spent FROM customers c JOIN carts ca ON c.id = ca.customer_id WHERE ca.status = \'paid\' GROUP BY c.name ORDER BY total_spent DESC LIMIT 5' => 'Top 5 Clientes (Valor Gasto)',
            'SELECT DATE_FORMAT(created_at, \'%Y-%m\') AS sales_month, SUM(total_price) AS total_sales FROM carts WHERE status = \'paid\' AND created_at >= CURDATE() - INTERVAL 6 MONTH GROUP BY sales_month ORDER BY sales_month ASC' => 'Vendas Mensais (Últimos 6 Meses)',
            'SELECT p.name, m.quantity AS stock_level FROM products p JOIN (SELECT product_id, SUM(quantity) AS quantity FROM stock_movements GROUP BY product_id) m ON p.id = m.product_id WHERE m.quantity < 10 ORDER BY stock_level ASC' => 'Produtos com Stock Baixo (< 10)',
        ];

        return $form
            ->schema([
                Components\Grid::make(2)->schema([
                    Components\Select::make('load_report')
                        ->label('Carregar Modelo Guardado')
                        ->options($this->savedReportOptions)
                        ->placeholder('Escolha um relatório guardado...')
                        ->reactive()->searchable()
                        ->afterStateUpdated(fn(?string $state) => $this->loadPrompt($state)),
                    Components\Select::make('suggestions')
                        ->label('Sugestões de Análise')
                        ->options($promptSuggestions)
                        ->placeholder('Ou escolha uma sugestão...')
                        ->reactive()
                        ->afterStateUpdated(fn(?string $state) => $this->fillPrompt($state)),
                ]),
                Components\Textarea::make('prompt')
                    ->label('Comando de Análise (ou SQL)')
                    ->placeholder('Escreva um comando (ex: "vendas por produto") ou cole um SQL (ex: "SELECT...")')
                    ->required()->rows(3)->columnSpanFull(),
                Components\Grid::make(2)->schema([
                    Components\DatePicker::make('start_date')->label('Data de Início (Opcional)'),
                    Components\DatePicker::make('end_date')->label('Data de Fim (Opcional)'),
                ]),
                Components\Grid::make(2)->schema([
                    Components\Radio::make('output_mode')
                        ->label('Modo de Saída Inicial')
                        ->options(['chart' => 'Gráfico', 'report' => 'Relatório (Tabela)'])
                        ->reactive()->default('report')->inline(),
                    Components\Select::make('chart_type')
                        ->label('Tipo de Gráfico')
                        ->options(['bar' => 'Barras', 'pie' => 'Pizza', 'line' => 'Linha', 'radar' => 'Radar'])
                        ->default('bar')
                        ->visible(fn($get) => $get('output_mode') === 'chart')
                        ->reactive(),
                ]),
            ])
            ->statePath('formData');
    }

    public function generate(): void
    {

        if ($this->chartDataColumn) {
            $this->chartDisplayLabel = $this->translateChartLabel($this->chartDataColumn);
        } else {
            $this->chartDisplayLabel = 'Valor';
        }

        $this->isGenerating = true;
        $this->form->validate();

        $this->resetResults();
        $prompt = $this->formData['prompt'];
        $startDate = $this->formData['start_date'];
        $endDate = $this->formData['end_date'];

        try {
            $finalPrompt = $prompt;
            if ($startDate || $endDate) {
                $finalPrompt .= " (Período: " . ($startDate ?? 'Início') . " até " . ($endDate ?? 'Fim') . ")";
            }

            $sqlQuery = "";

            if (Str::startsWith(strtoupper(trim($finalPrompt)), 'SELECT')) {
                Notification::make()->title('A executar SQL direto...')->info()->send();
                $sqlQuery = $finalPrompt;
            } else {
                Notification::make()->title('A IA está a pensar...')->info()->send();
                $sqlQuery = $this->ai->generateQueryFromPrompt($finalPrompt);
            }

            if (!$this->ai->isSafeQuery($sqlQuery)) {
                Notification::make()->title('Consulta Insegura')->body('A IA tentou executar um comando não permitido (ex: UPDATE, DELETE) e foi bloqueada.')->danger()->send();
                return;
            }

            Notification::make()->title('A executar consulta...')->info()->send();
            $results = DB::select($sqlQuery);
            $data = json_decode(json_encode($results), true);

            if (empty($data)) {
                Notification::make()->title('Nenhum Resultado')->body('A sua consulta foi executada, mas não retornou dados.')->warning()->send();
                return;
            }

            // --- ✅✅✅ LÓGICA DE DETECÇÃO DE COLUNA ROBUSTA (A CORREÇÃO FINAL) ✅✅✅ ---
            $this->reportColumns = array_keys($data[0]);
            $firstRow = $data[0];

            $this->chartLabelColumn = null;
            $this->chartDataColumn = null;
            $labelCandidates = [];
            $dataCandidates = [];

            // Funções de ajuda
            $isID = fn($col) => Str::endsWith(strtolower($col), '_id') || Str::endsWith(strtolower($col), 'id') || Str::contains(strtolower($col), 'uuid');
            $isLabelName = fn($col) => Str::contains(strtolower($col), ['name', 'nome', 'title', 'mes']);
            $isDataValue = fn($col) => Str::contains(strtolower($col), ['total', 'sum', 'count', 'valor', 'price']);

            foreach ($this->reportColumns as $column) {
                // 1. Prioriza colunas de texto/data que não são IDs (para Labels/Eixo X)
                if (is_string($firstRow[$column]) && !$isID($column)) {
                    $labelCandidates[] = $column;
                }

                // 2. Prioriza colunas numéricas (para Dados/Eixo Y)
                if (is_numeric($firstRow[$column]) || is_int($firstRow[$column]) || is_float($firstRow[$column])) {
                    $dataCandidates[] = $column;
                }
            }

            // Detecção Final:
            // Procura o melhor nome (contém 'name', 'mes', 'title')
            $bestLabel = collect($labelCandidates)->first(fn($col) => $isLabelName($col));
            // Procura o melhor valor (contém 'total', 'sum', 'valor')
            $bestData = collect($dataCandidates)->first(fn($col) => $isDataValue($col));

            // Atribuição:
            $this->chartLabelColumn = $bestLabel ?: ($labelCandidates[0] ?? ($this->reportColumns[0] ?? null));
            $this->chartDataColumn = $bestData ?: ($dataCandidates[0] ?? ($this->reportColumns[1] ?? ($this->reportColumns[0] ?? null)));

            // Garante que a coluna de dados é diferente da coluna de labels
            if ($this->chartDataColumn === $this->chartLabelColumn && count($dataCandidates) > 1) {
                // Se coincidir, e tivermos mais de uma coluna numérica, escolhe a segunda candidata numérica.
                $this->chartDataColumn = $dataCandidates[1];
            }

            // --- FIM DA LÓGICA DE DETECÇÃO ---


            // Agora, a verificação de 'chartsDisabled' usa as colunas detetadas
            if (count($this->reportColumns) < 2 || is_null($this->chartLabelColumn) || is_null($this->chartDataColumn) || $this->chartLabelColumn === $this->chartDataColumn) {
                $this->chartsDisabled = true;
                $this->currentMode = 'report';
                $this->currentType = 'visualization';
                $this->formData['output_mode'] = 'report';
                Notification::make()->title('Aviso: Gráficos desativados')
                    ->body('A consulta não retornou colunas de texto e número válidas para plotar o gráfico. A exibir como tabela.')
                    ->warning()->send();
            } else {
                $this->chartsDisabled = false;
                $this->currentMode = $this->formData['output_mode'];
                $this->currentType = ($this->currentMode === 'chart')
                    ? $this->formData['chart_type']
                    : 'visualization';
            }

            $this->reportId = 'report_' . Str::uuid();
            $this->reportTitle = $prompt;
            $this->reportData = $data;

            Cache::put($this->reportId, [
                'data' => $this->reportData,
                'columns' => $this->reportColumns,
                'title' => $this->reportTitle
            ], now()->addMinutes(60));

            Notification::make()->title('Relatório Gerado!')->success()->send();
        } catch (Exception $e) {
            Notification::make()->title('Erro ao Gerar Relatório')->body($e->getMessage())->danger()->send();
        } finally {
            $this->isGenerating = false;
        }
    }

    // --- Métodos de Ação (Com as novas lógicas de "Abas") ---

    // (#4) Preenche o prompt com a sugestão
    public function fillPrompt(?string $state): void
    {
        if ($state) {
            $this->formData['prompt'] = $state;
            $this->formData['load_report'] = null;
        }
    }

    // (#1) Carrega o prompt do relatório guardado
    public function loadPrompt(?string $reportId): void
    {
        if ($reportId) {
            $report = SavedReport::find($reportId);
            if ($report) {
                $this->formData['prompt'] = $report->prompt;
                $this->formData['suggestions'] = null;
            }
        }
    }

    // (#1) Recarrega a lista de relatórios guardados
    protected function loadSavedReports(): void
    {
        if (Auth::check()) {
            $this->savedReportOptions = SavedReport::where('user_id', Auth::id())
                ->orderBy('name')->pluck('name', 'id')->toArray();
        } else {
            $this->savedReportOptions = [];
        }
    }

    // (#1) Ação de Guardar Relatório (Mantida igual, vai funcionar)
    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveReport')
                ->label('Guardar Relatório')
                ->icon('heroicon-o-bookmark')
                ->color('gray')
                ->visible(fn() => !empty($this->formData['prompt']))
                ->modalHeading('Guardar Relatório/Modelo')
                ->modalDescription('Dê um nome a este prompt para o reutilizar mais tarde.')
                ->form([
                    Components\TextInput::make('name')
                        ->label('Nome do Relatório')
                        ->placeholder('Ex: Vendas Mensais por Produto')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        SavedReport::create([
                            'user_id' => Auth::id(),
                            'recipient_email' => Auth::user()->email,
                            'is_scheduled' => true,
                            'frequency' => 1,
                            'name' => $data['name'],
                            'prompt' => $this->formData['prompt'],
                        ]);
                        $this->loadSavedReports();
                        Notification::make()->title('Relatório Guardado!')->success()->send();
                    } catch (Exception $e) {
                        Notification::make()->title('Erro ao Guardar')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }

    /**
     * ✅ NOVO: Controla a aba principal (Report vs Analysis)
     */
    public function setMainView(string $view): void
    {
        $this->mainView = $view;

        // Se clicar em "Análise"
        if ($view === 'analysis' && is_null($this->analysisResult) && !$this->analysisLoading) {
            $this->analyzeResults();
        }

        // ✅ NOVO: Se clicar em "Previsão"
        if ($view === 'forecast' && is_null($this->forecastData) && !$this->forecastLoading) {
            $this->runForecast();
        }
    }

    /**
     * ✅ ATUALIZADO: Controla a sub-aba (Tabela vs Gráfico)
     */
    public function setViewMode($mode, $type = 'visualization')
    {
        $this->mainView = 'report'; // Força a volta para a aba de Relatório
        $this->currentMode = $mode;
        $this->currentType = $type;
    }

    /**
     * ✅ ATUALIZADO: Ação de Analisar Resultados
     * (Não abre mais modal, apenas troca a aba e carrega os dados)
     */
    public function analyzeResults(): void
    {
        if (!$this->reportData) {
            Notification::make()->title('Sem dados para analisar')->warning()->send();
            return;
        }

        $this->analysisResult = null;
        $this->analysisLoading = true;
        $this->mainView = 'analysis'; // Define a aba ativa

        try {
            $this->analysisResult = $this->ai->getAnalysisForData(
                $this->reportData,
                $this->reportTitle
            );
        } catch (Exception $e) {
            $this->analysisResult = "Ocorreu um erro ao gerar a análise: " . $e->getMessage();
        } finally {
            $this->analysisLoading = false;
        }
    }

    public function exportPdf()
    {
        if (!$this->reportId || !Cache::has($this->reportId)) {
            Notification::make()->title('Sessão Expirada')->warning()->send();
            return;
        }
        $reportData = Cache::get($this->reportId);
        $viewName = 'reports.view-template';
        $pdf = PDF::loadView($viewName, $reportData);
        if (count($reportData['columns']) > 6) {
            $pdf->setPaper('a4', 'landscape');
        }
        return response()->streamDownload(fn() => print($pdf->output()), 'relatorio.pdf');
    }

    public function exportXlsx()
    {
        if (!$this->reportId || !Cache::has($this->reportId)) {
            Notification::make()->title('Sessão Expirada')->warning()->send();
            return null;
        }
        return Excel::download(new ReportExport($this->reportId), 'relatorio.xlsx');
    }

    public function exportCsv()
    {
        if (!$this->reportId || !Cache::has($this->reportId)) {
            Notification::make()->title('Sessão Expirada')->warning()->send();
            return null;
        }
        $reportData = Cache::get($this->reportId);
        $data = $reportData['data'];
        $columns = $reportData['columns'];
        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=relatorio.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        return response()->stream($callback, 200, $headers);
    }

    private function isSafeQuery(string $sql): bool
    {
        $trimmedSql = strtoupper(trim($sql, " \t\n\r\0\x0B;()"));
        if (!Str::startsWith($trimmedSql, 'SELECT')) return false;
        $forbiddenKeywords = ['DELETE', 'UPDATE', 'INSERT', 'DROP', 'ALTER', 'TRUNCATE', 'CREATE', 'REPLACE', 'GRANT', 'REVOKE'];
        foreach ($forbiddenKeywords as $keyword) {
            if (str_contains(strtoupper($sql), $keyword)) return false;
        }
        return true;
    }

    public function runForecast(): void
    {
        $this->forecastLoading = true;
        $this->forecastData = null;
        $this->forecastAnalysis = null;
        $this->mainView = 'forecast'; // Garante que a aba está ativa

        try {
            // Chama a nova função 4 do serviço
            $response = $this->ai->generateSalesForecast();

            $this->forecastData = $response;
            $this->forecastAnalysis = $response['analysis'];
        } catch (Exception $e) {
            Notification::make()
                ->title('Erro ao Gerar Previsão')
                ->body($e->getMessage())
                ->danger()
                ->send();
            $this->forecastAnalysis = "Ocorreu um erro ao gerar a previsão: " . $e->getMessage();
        } finally {
            $this->forecastLoading = false;
        }
    }

    private function resetResults()
    {
        $this->reportId = null;
        $this->reportTitle = null;
        $this->reportData = null;
        $this->reportColumns = null;
        $this->chartsDisabled = false;
        $this->analysisResult = null;
        $this->analysisLoading = false;
        $this->mainView = 'report';
        $this->forecastData = null;
        $this->forecastAnalysis = null;
        $this->forecastLoading = false;

        // ✅ Limpar as colunas detetadas
        $this->chartLabelColumn = null;
        $this->chartDataColumn = null;
        $this->chartDisplayLabel = null;
    }
}
