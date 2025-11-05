<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Carbon;
use Filament\Forms;

class AnalyticsDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.analytics-dashboard';

    // UI Configuration
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Análise Geral da Loja';
    protected static bool $shouldRegisterNavigation = true;

    
    // ------------------------------------------------
    // Global State for Date Filtering
    // ------------------------------------------------

    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
    ];

    public function mount(): void
    {
        // Define o padrão: Últimos 30 dias
        $this->data['start_date'] = Carbon::now()->subDays(30)->toDateString();
        $this->data['end_date'] = Carbon::now()->toDateString();
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filtro de Período')
                    ->description('Selecione um intervalo de datas para aplicar a todos os gráficos e cards abaixo.')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Data Inicial')
                            ->default(Carbon::now()->subDays(30)->toDateString())
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->updatedData()), // Aciona a atualização

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->default(Carbon::now()->toDateString())
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn () => $this->updatedData()) // Aciona a atualização
                            ->minDate(fn (Forms\Get $get) => $get('start_date') ?: null),

                    ])
                    ->columns(2)
                    ->compact()
            ])
            ->statePath('data');
    }

    // Método chamado quando o estado do filtro muda
    public function updatedData(): void
    {
        // ✅ Emite um evento global que todos os widgets Livewire escutam
        $this->dispatch('dateRangeUpdated', [
            'startDate' => $this->data['start_date'],
            'endDate' => $this->data['end_date'],
        ]);
        
        $this->form->fill($this->data);
    }
}