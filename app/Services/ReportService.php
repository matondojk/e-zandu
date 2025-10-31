<?php

namespace App\Services;

use PDF; // (barryvdh/laravel-dompdf)
use \Exception;
use Illuminate\Support\Facades\File;

class ReportService
{
    /**
     * Gera um PDF a partir dos dados do relatório e guarda-o no disco.
     * Retorna o caminho completo do ficheiro guardado.
     */
    public function generatePdf(array $data, array $columns, string $title): string
    {
        try {
            // Prepara os dados para a view do PDF
            $viewData = [
                'data' => $data,
                'columns' => $columns,
                'title' => $title
            ];

            // Carrega a view 'reports.view-template' (a mesma que usamos na página)
            // Se o seu template estiver noutro sítio, ajuste o nome 'reports.view-template'
            $pdf = PDF::loadView('reports.view-template', $viewData);
            $pdf->setPaper('a4', 'landscape'); // Deitar o papel para tabelas largas

            // Define um nome e caminho temporário
            // Vamos usar a pasta 'storage/app/reports'
            $filename = 'report-' . \Illuminate\Support\Str::slug($title) . '-' . time() . '.pdf';
            $directory = storage_path('app/reports');
            
            // Cria o diretório se não existir
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $path = $directory . '/' . $filename;
            
            // Salva o PDF no disco
            $pdf->save($path);

            return $path; // Retorna o caminho *completo* do servidor

        } catch (Exception $e) {
            \Log::error("Erro ao gerar PDF: " . $e->getMessage());
            throw $e;
        }
    }
}