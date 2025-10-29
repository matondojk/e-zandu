<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
// Importar um Model existente para usar na Query de fallback
use App\Models\Cart; 

class TopPagesTableWidget extends BaseWidget
{
    protected static ?string $heading = 'Páginas Mais Vistas (Top 10)';
    protected static ?int $sort = 1;
    protected static ?string $pollingInterval = null; 
    protected int | string | array $columnSpan = 'full'; // <-- CORREÇÃO: Ocupa 100% da largura

    // OBTEM OS DADOS DA API EXTERNA (Dados Reais)
    protected function getTableData(): array
    {
        try {
            $period = Period::days(30);

            // Usamos o método fetchMostVisitedPages que é mais simples
            $topPages = Analytics::fetchMostVisitedPages($period, 10); 

            // O Filament precisa de um array de arrays ou array de objetos para dados não-Eloquent
            return $topPages->toArray(); 
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro no Widget Tabela GA: " . $e->getMessage());
            return [['fullPageUrl' => 'Erro ao Carregar Dados', 'pageTitle' => 'Verifique o .env', 'screenPageViews' => 0]];
        }
    }

    // CORREÇÃO: Fornece uma Query Eloquent de fallback para satisfazer o requisito do Filament.
    // Usamos um Model existente (Cart) e garantimos que a query não retorna dados (limit 0).
    protected function getTableQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        // Força a query a usar um Model e a retornar 0 resultados.
        // O getTableData() é que fornecerá os dados reais.
        return Cart::query()->limit(0); 
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('fullPageUrl')
                ->label('URL da Página')
                ->sortable()
                ->searchable(),
            TextColumn::make('pageTitle') 
                ->label('Título da Página')
                ->sortable()
                ->searchable(),
            TextColumn::make('screenPageViews')
                ->label('Visualizações')
                ->sortable()
                ->alignEnd()
                ->formatStateUsing(fn ($state) => number_format($state, 0, '.', ','))
        ];
    }
    
    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }
}
