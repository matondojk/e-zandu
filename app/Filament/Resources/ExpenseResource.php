<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter; // Para o filtro de data
use App\Models\Setting; // Adicionado para detalhes da empresa no PDF
use Barryvdh\DomPDF\Facade\Pdf; // Adicionado para geração de PDF
use Filament\Tables\Actions\ExportAction; // Adicionado para exportação de Excel
use App\Filament\Exports\ExpenseExporter; // Certifique-se de criar esta classe
use App\Exports\ExpenseExport; // 👈 NOVA CLASSE DE EXPORTAÇÃO (Você deve criá-la) 
use Maatwebsite\Excel\Facades\Excel; // 👈 Adicionado para exportar no Action

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    // ✅ Configuração da Navegação
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Despesas Operacionais';
    protected static ?string $modelLabel = 'Despesa';
    protected static ?string $pluralModelLabel = 'Despesas';
    protected static ?string $navigationGroup = 'Financeiro'; // Novo grupo
    protected static ?int $navigationSort = 6;
    protected static ?string $slug = 'despesas-operacionais'; // URL em português

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes da Despesa')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Descrição da Despesa')
                            ->placeholder('Ex: Renda do escritório, Marketing, Salários')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Valor (Kz)')
                            ->numeric()
                            ->prefix('Kz')
                            ->required(),

                        Forms\Components\DatePicker::make('expense_date')
                            ->label('Data da Despesa')
                            ->required()
                            ->default(now()),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notas Adicionais')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Data da Despesa')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registo')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // ✅ AÇÃO 1: Exportar Excel (Apenas ícone, cor do PDF)
                Tables\Actions\Action::make('export_excel_custom')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->iconButton()
                ->action(function ($livewire) {
                    // Obtém a query filtrada da tabela
                    $query = $livewire->getFilteredTableQuery(); 
                    
                    // Retorna a exportação usando o Laravel Excel/Maatwebsite
                    // Chama a classe de exportação customizada com a query
                    return Excel::download(
                        new ExpenseExport($query), // Sua classe de exportação customizada
                        'lista-despesas-' . date('Y-m-d') . '.xlsx'
                    );
                }),

                // ✅ AÇÃO 2: Download da Lista em PDF (Apenas ícone, cor do PDF)
                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Baixar Lista (PDF)')
                    ->icon('heroicon-o-document-arrow-down') // Ícone de documento
                    ->color('gray')
                    ->iconButton() // Transforma em botão de ícone
                    ->action(function ($livewire) {
                        // ... (sua lógica de geração de PDF)
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();
                        $settings = Setting::first();
                        $totalExpenses = $records->sum('amount');

                        $pdf = Pdf::loadView('pdfs.expenses-list', [
                            'records' => $records,
                            'settings' => $settings,
                            'totalExpenses' => $totalExpenses
                        ])->setPaper('a4', 'portrait');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'lista-despesas-' . date('Y-m-d') . '.pdf');
                    }),
            ])
            ->filters([
                // Filtro para o Dashboard (embora o dashboard tenha o seu próprio filtro global)
                Filter::make('expense_date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')->label('De'),
                        Forms\Components\DatePicker::make('date_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expense_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download_pdf_details')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->iconButton()
                    ->action(function (Expense $record) {
                        $settings = Setting::first();

                        // ROTA: resources/views/pdfs/expense-details.blade.php
                        $pdf = Pdf::loadView('pdfs.expense-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'despesa-' . $record->id . '.pdf');
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('expense_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
