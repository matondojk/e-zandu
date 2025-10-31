<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
// use App\Services\InvoiceService; // <-- REMOVIDO: Você está correto, não devemos usar isto aqui.
use \Exception;
use PDF; // ✅ ADICIONADO: Importar a fachada do PDF diretamente.

class ReportViewController extends Controller
{
    /**
     * ✅ CORREÇÃO: Removido o construtor e a injeção do InvoiceService.
     * O controlador agora não tem dependências de construtor, 
     * o que resolve o 'BindingResolutionException'.
     */
    public function __construct()
    {
        // Construtor vazio
    }

    public function show(Request $request)
    {
        $reportId = $request->query('reportId');
        $mode = $request->query('mode'); // 'report' ou 'chart'
        $type = $request->query('type'); // 'visualization', 'pdf', ou 'bar', 'pie'

        $reportData = Cache::get($reportId);

        if (!$reportData) {
            return response("Erro: Relatório expirado ou não encontrado. Por favor, gere novamente.", 404);
        }

        $data = $reportData['data'];
        $columns = $reportData['columns'];
        $title = $reportData['title'];

        // --- Lógica de Renderização ---

        // MODO 1: Relatório PDF
        if ($mode === 'report' && $type === 'pdf') {
            try {
                $viewName = 'reports.view-template'; 
                $viewData = ['data' => $data, 'columns' => $columns, 'title' => $title];

                // ✅ CORREÇÃO: Usar a fachada 'PDF' diretamente.
                $pdf = PDF::loadView($viewName, $viewData);
                
                // Definir orientação para 'Landscape' (Paisagem) se houver muitas colunas
                if(count($columns) > 6) {
                    $pdf->setPaper('a4', 'landscape');
                }
                
                // Retorna o PDF 'inline' para o iframe
                return $pdf->stream('relatorio.pdf');

            } catch (Exception $e) {
                // Corrigido o ParseError anterior
                return response("Erro ao gerar PDF: " . $e->getMessage(), 500); 
            }
        }
        
        // MODO 2: Gráfico (bar, pie, etc)
        if ($mode === 'chart') {
            return view('reports.view', [
                'reportId' => $reportId,
                'mode' => 'chart',
                'type' => $type,
            ]);
        }
        
        // MODO 3: Relatório HTML (Visualização)
        return view('reports.view', [
            'reportId' => $reportId,
            'mode' => 'report',
            'type' => 'visualization',
        ]);
    }
}