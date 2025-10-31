<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Inteligente</title>
    
    {{-- CSS Básico para o Relatório/Gráfico --}}
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; }
        .container { padding: 20px; }
        h1 { font-size: 24px; margin-top: 0; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        
        /* Estilos da Tabela (Modo Visualização) */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f9f9f9; font-weight: 600; }
        tr:nth-child(even) { background-color: #fdfdfd; }
        
        /* Estilos do Gráfico (Modo Gráfico) */
        .chart-container { width: 95%; max-width: 1000px; margin: 20px auto; }
    </style>
    
    {{-- Script do Chart.js (Necessário para Gráficos) --}}
    @if($mode === 'chart')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endif
</head>
<body>
    @php
        // Puxar os dados do Cache. O Controller já validou que existe.
        $reportData = Cache::get($reportId);
        $data = $reportData['data'];
        $columns = $reportData['columns'];
        $title = $reportData['title'];
    @endphp

    <div class="container">
        <h1>Relatório: {{ $title }}</h1>

        {{-- RENDERIZAÇÃO MODO GRÁFICO --}}
        @if($mode === 'chart')
            <div class="chart-container">
                <canvas id="reportChart"></canvas>
            </div>
            
            @php
                // Preparar dados para o Chart.js
                // Assumir que a primeira coluna é o Label (ex: "Produto A")
                // e a segunda coluna é o Valor (ex: 5000)
                $chartLabels = json_encode(array_column($data, $columns[0]));
                $chartData = json_encode(array_column($data, $columns[1]));
            @endphp
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('reportChart').getContext('2d');
                    new Chart(ctx, {
                        type: '{{ $type }}', // 'bar', 'pie', 'line', etc.
                        data: {
                            labels: {!! $chartLabels !!},
                            datasets: [{
                                label: '{{ $columns[1] }}', // Nome da coluna de valor
                                data: {!! $chartData !!},
                                // Cores dinâmicas
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)',
                                    'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)',
                                    'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)',
                                    'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                });
            </script>
        
        {{-- RENDERIZAÇÃO MODO RELATÓRIO HTML --}}
        @else 
            @include('reports.view-template', $reportData)
        @endif
        
    </div>
</body>
</html>