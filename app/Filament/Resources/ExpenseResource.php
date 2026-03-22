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
use Filament\Tables\Filters\Filter; 
use App\Models\Setting; 
use Barryvdh\DomPDF\Facade\Pdf; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\ExpenseExport; // Sua classe de exportação

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    // ✅ Configuração da Navegação
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Despesas Operacionais';
    protected static ?string $modelLabel = 'Despesa';
    protected static ?string $pluralModelLabel = 'Despesas';
    protected static ?string $navigationGroup = 'Financeiro'; 
    protected static ?string $slug = 'despesas-operacionais'; 

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
            ->defaultSort('expense_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor (Kz)')
                    ->sortable()
                    // ✅ NOVO: Formato financeiro
                    ->formatStateUsing(fn($state): string => number_format($state, 2, ',', '.') . ' Kz')
                    ->alignEnd()
                    ->color('danger')
                    ->weight('semibold'),

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
                // ✅ AÇÃO 1: Exportar Excel (Icon Button)
                Tables\Actions\Action::make('export_excel_custom')
                ->label('Exportar Excel')
                ->icon('heroicon-o-table-cells')
                ->color('gray')
                ->iconButton()
                ->action(function ($livewire) {
                    $query = $livewire->getFilteredTableQuery(); 
                    
                    return Excel::download(
                        new ExpenseExport($query),
                        'lista-despesas-' . date('Y-m-d') . '.xlsx'
                    );
                }),

                // ✅ AÇÃO 2: Download da Lista em PDF (Icon Button)
                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Baixar Lista (PDF)')
                    ->icon('heroicon-o-document-arrow-down') 
                    ->color('gray')
                    ->iconButton()
                    ->action(function ($livewire) {
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
                // ✅ AÇÃO 1: Download de Detalhes PDF (Icon Button)
                Tables\Actions\Action::make('download_pdf_details')
                    ->label('PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->iconButton()
                    ->action(function (Expense $record) {
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.expense-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'despesa-' . $record->id . '.pdf');
                    }),
                
                // ✅ AÇÃO 2: Editar (Icon Button)
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->icon('heroicon-o-pencil'),
                    
                // ✅ AÇÃO 3: Deletar (Icon Button)
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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