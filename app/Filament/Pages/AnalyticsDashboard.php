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
    protected static ?string $slug = 'analytics-dashboard';

    // Global State for Date Filtering (Propriedade usada na view Blade)
    public ?array $data = [
        'start_date' => null,
        'end_date' => null,
    ];

    public function mount(): void
    {
        // 1. Define o padrão: Últimos 30 dias
        if (empty($this->data['start_date'])) {
            $this->data['start_date'] = Carbon::now()->subDays(30)->toDateString();
        }
        if (empty($this->data['end_date'])) {
            $this->data['end_date'] = Carbon::now()->toDateString();
        }
        
        // 2. Preenche o formulário
        $this->form->fill($this->data);
        
        // ⭐️ CORREÇÃO ESSENCIAL: Dispara o evento Livewire para enviar as datas padrão aos widgets
        $this->updatedData(); 
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
                            // ⭐️ CORREÇÃO: REMOVER live(onBlur: true) e usar SÓ o afterStateUpdated
                            ->afterStateUpdated(fn () => $this->updatedData()),

                        DatePicker::make('end_date')
                            ->label('Data Final')
                            ->default(Carbon::now()->toDateString())
                            // ⭐️ CORREÇÃO: REMOVER live(onBlur: true) e usar SÓ o afterStateUpdated
                            ->afterStateUpdated(fn () => $this->updatedData())
                            ->minDate(fn (Forms\Get $get) => $get('start_date') ?: null),

                    ])
                    ->columns(2)
                    ->compact()
            ])
            ->statePath('data');
    }

    // ✅ MÉTODO FINAL: Dispara o evento Livewire para todos os widgets
    public function updatedData(): void
    {
        // 1. O evento Livewire usa camelCase: 'startDate', 'endDate'
        $this->dispatch('dateRangeUpdated', [
            'startDate' => $this->data['start_date'],
            'endDate' => $this->data['end_date'],
        ]);
        
        // 2. Garante que o estado interno do formulário é mantido
        $this->form->fill($this->data);
    }
    
    protected function getContentView(): string
    {
        return 'filament.pages.analytics-dashboard';
    }
}