<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Spatie\Analytics\Facades\Analytics;
use Spatie\Analytics\Period;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AnalyticsStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0; // Coloca no topo
    protected static ?string $pollingInterval = null; // Desativa o polling

    protected function getStats(): array
    {
        try {
            $period = Period::days(30);

            // 1. Busca os dados brutos de users, sessions e views
            // fetchVisitorsAndPageViews é um método simplificado do Spatie
            $sessionsData = Analytics::fetchVisitorsAndPageViews($period);
            
            // 2. Busca o total de conversões (usando o método get genérico)
            $conversionData = Analytics::get($period, ['conversions']);
            
            // 3. Busca a Taxa de Rejeição Média (bounceRate)
            $bounceRateData = Analytics::get($period, ['bounceRate'], ['date']);
            
            $totalUsers = $sessionsData->sum('activeUsers');
            $totalSessions = $sessionsData->sum('sessions');
            $totalConversions = $conversionData->sum('conversions');
            $avgBounceRate = $bounceRateData->avg('bounceRate') * 100; // Multiplica por 100
            
            $conversionRate = ($totalSessions > 0) ? ($totalConversions / $totalSessions) * 100 : 0;

        } catch (\Exception $e) {
            Log::error("Erro no Widget de Stats GA: " . $e->getMessage());
            return [
                Stat::make('Erro de Conexão', 'Verifique ANALYTICS_PROPERTY_ID e JSON.')
                    ->descriptionIcon('heroicon-o-exclamation-triangle') // ÍCONE CORRIGIDO
                    ->color('danger'),
            ];
        }

        return [
            Stat::make('Utilizadores (30D)', number_format($totalUsers, 0, '.', ','))
                ->description('Utilizadores Únicos Ativos')
                ->descriptionIcon('heroicon-o-user') // ÍCONE CORRIGIDO
                ->color('success'),
                
            Stat::make('Sessões Totais (30D)', number_format($totalSessions, 0, '.', ','))
                ->description('Total de Visitas à Loja')
                ->descriptionIcon('heroicon-o-clock') // ÍCONE CORRIGIDO
                ->color('info'),

            Stat::make('Taxa de Rejeição Média', number_format($avgBounceRate, 2) . '%')
                ->description('Saídas Imediatas')
                ->descriptionIcon('heroicon-o-arrow-path') // <-- ÍCONE SUBSTITUÍDO (arrow-path é mais seguro que arrow-path-down-left)
                ->color('warning'),
                
            Stat::make('Conversões (30D)', number_format($totalConversions, 0, '.', ','))
                ->description('Eventos de conversão')
                ->descriptionIcon('heroicon-o-check-badge') // ÍCONE CORRIGIDO
                ->color('success'),

            Stat::make('Taxa de Conversão', number_format($conversionRate, 2) . '%')
                ->description('Conversões / Sessões')
                ->descriptionIcon('heroicon-o-chart-bar') // ÍCONE CORRIGIDO
                ->color($conversionRate > 5 ? 'success' : 'danger'),
        ];
    }
}
