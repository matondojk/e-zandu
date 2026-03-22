<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StockResource\Pages;
use App\Filament\Resources\StockResource\RelationManagers;
use App\Models\Stock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class StockResource extends Resource
{
    protected static ?string $model = Stock::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Armazéns / Estoques';
    protected static ?string $modelLabel = 'Armazém';
    protected static ?string $pluralModelLabel = 'Armazéns';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?string $slug = 'armazens'; // URL em português


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detalhes do Armazém')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do Armazém')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Ex: Armazém Principal, Loja Física, Depósito Secundário'),

                        Forms\Components\Toggle::make('is_default')
                            ->label('Armazém Padrão')
                            ->helperText('Marque se este for o local de estoque principal.'),

                        Forms\Components\Textarea::make('location')
                            ->label('Localização Física / Endereço')
                            ->rows(2)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ✅ REMOVIDO: O defaultSort foi movido para getEloquentQuery()
            // ->defaultSort('updated_at', 'desc') 

            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Armazém')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_stock')
                    ->label('Total de Itens')
                    ->state(fn(Stock $record): int => $record->movements()->sum('quantity'))
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->color('info')
                    ->alignEnd()
                    ->sortable(false),

                Tables\Columns\TextColumn::make('location')
                    ->label('Localização')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\TernaryFilter::make('is_default')
                    ->label('Armazém Padrão')
                    ->boolean()
                    ->trueLabel('Apenas Padrão')
                    ->falseLabel('Apenas Não-Padrão')
                    ->native(false),
            ])
            ->headerActions([
                // ✅ AÇÃO 1: Botão para Download da Lista de Armazéns
                Tables\Actions\Action::make('export_pdf_list')
                    ->label('Baixar Lista (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->iconButton()
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $records = $query->get();
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.stocks-list', [
                            'records' => $records,
                            'settings' => $settings
                        ])->setPaper('a4', 'portrait');

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'lista-armazens-' . date('Y-m-d') . '.pdf');
                    }),
            ])
            ->actions([

                Tables\Actions\Action::make('download_pdf_details')
                    ->label('Detalhes (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->iconButton()
                    ->action(function (Stock $record) {
                        $settings = Setting::first();

                        $pdf = Pdf::loadView('pdfs.stock-details', [
                            'record' => $record,
                            'settings' => $settings
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'armazem-' . $record->name . '.pdf');
                    }),

                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStocks::route('/'),
            'create' => Pages\CreateStock::route('/create'),
            'edit' => Pages\EditStock::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc');
    }
}
