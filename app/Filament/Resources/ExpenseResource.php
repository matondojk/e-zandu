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
