<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Illuminate\Support\Carbon;
use Filament\Forms;

// Estende BaseDashboard e usa HasFiltersForm para gerenciamento automático do filtro
class AnalyticsDashboard extends BaseDashboard
{
    use HasFiltersForm;
    // O Livewire Forms está implícito no BaseDashboard.

    protected static string $view = 'filament.pages.analytics-dashboard';

    // UI Configuration
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Análise Geral da Loja';
    protected static bool $shouldRegisterNavigation = true;

    // Método oficial para definir os campos de filtro
    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtro de Período')
                    ->description('Selecione um intervalo de datas para aplicar a todos os gráficos e cards abaixo.')
                    ->schema([
                        DatePicker::make('startDate') // Chave usada no $this->filters
                            ->label('Data Inicial')
                            ->default(Carbon::now()->subDays(30)->toDateString()),

                        DatePicker::make('endDate') // Chave usada no $this->filters
                            ->label('Data Final')
                            ->default(Carbon::now()->toDateString())
                            ->minDate(fn (Forms\Get $get) => $get('startDate') ?: null),

                    ])
                    ->columns(2)
                    ->compact(),
            ]);
    }
    
    // Força o uso da view customizada (para o layout de grelha manual)
    protected function getContentView(): string
    {
        return 'filament.pages.analytics-dashboard';
    }

    // mount(), updatedData(), makeForm() foram removidos. HasFiltersForm gere isso.
}