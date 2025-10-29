<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

// ✅ Imports Adicionados
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Forms\Components\DatePicker;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Categorias';
    protected static ?string $modelLabel = 'Categoria';
    protected static ?string $pluralModelLabel = 'Categorias';
    protected static ?int $navigationSort = 3; 
    protected static ?string $navigationGroup = 'Catálogo & Inventário';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ✅ Nova Estrutura de Formulário
                Grid::make(3)->schema([
                    // --- COLUNA PRINCIPAL ---
                    Group::make()
                        ->schema([
                            Section::make('Detalhes da Categoria')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome da Categoria')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug (URL amigável)')
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),
                                    
                                    Forms\Components\TextInput::make('icon')
                                        ->label('Ícone (Heroicons)')
                                        ->helperText('Ex: heroicon-o-computer-desktop. Ícone para uso futuro.')
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->columnSpan(2),

                    // --- COLUNA LATERAL ---
                    Group::make()
                        ->schema([
                            Section::make('Descrição')
                                ->schema([
                                    Forms\Components\Textarea::make('description')
                                        ->label('Descrição')
                                        ->rows(10) // Mais espaço
                                        ->columnSpanFull(),
                                ])
                        ])
                        ->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->alignCenter()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ✅ Pedido 2: Mais Filtros
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Registada desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Registada até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                
                TernaryFilter::make('products')
                    ->label('Produtos')
                    ->boolean()
                    ->trueLabel('Apenas com produtos')
                    ->falseLabel('Apenas sem produtos')
                    ->queries(
                        true: fn (Builder $query) => $query->has('products'),
                        false: fn (Builder $query) => $query->doesntHave('products'),
                    )
                    ->native(false),
            ])
            ->actions([
                // ✅ Pedido 1: Ações como iconButton
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            // ✅ Adicionada ordenação padrão
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('products');
    }
}