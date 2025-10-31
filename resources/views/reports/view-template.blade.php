{{-- 
    Este ficheiro é um sub-template.
    Ele contém APENAS o HTML da tabela.
    É usado por:
    1. intelligent-analysis.blade.php (para renderizar na tela)
    2. IntelligentAnalysis.php (para gerar o PDF)
--}}

<style>
    /* Estilos que o DOMPDF precisa */
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    .report-table-wrapper { width: 100%; overflow-x: auto; }
    .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .report-table th, .report-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .report-table th { background-color: #f0f0f0; }
</style>

@if(isset($title) && $title)
    <h2 style="font-size: 18px; text-align: center;">{{ $title }}</h2>
@endif

<div class="report-table-wrapper">
    <table class="report-table">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if(empty($data))
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align: center;">Nenhum dado encontrado.</td>
                </tr>
            @else
                @foreach($data as $row)
                    <tr>
                        @foreach($columns as $column)
                            <td>{{ $row[$column] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>