<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

// Imports Adicionados
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    // Configurações de Navegação
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Marcas';
    protected static ?string $modelLabel = 'Marca';
    protected static ?string $pluralModelLabel = 'Marcas';
    protected static ?int $navigationSort = 4; // Depois de Categorias (3)
    protected static ?string $navigationGroup = 'Catálogo & Inventário';
    protected static ?string $slug = 'marcas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    // --- COLUNA PRINCIPAL ---
                    Group::make()
                        ->schema([
                            Section::make('Detalhes da Marca')
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Nome da Marca')
                                        ->required()
                                        ->maxLength(255)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('slug')
                                        ->label('Slug (URL amigável)')
                                        ->disabled()
                                        ->dehydrated()
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    Forms\Components\Textarea::make('description')
                                        ->label('Descrição')
                                        ->rows(10)
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->columnSpan(2),

                    // --- COLUNA LATERAL ---
                    Group::make()
                        ->schema([
                            Section::make('Logótipo')
                                ->schema([
                                    FileUpload::make('logo_path')
                                        ->label('Logótipo')
                                        ->disk('public') // <-- ADICIONADO
                                        ->directory('brands-logos') // Salva em [seu_disco_public]/brands-logos
                                        ->image()
                                        ->imageEditor()
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
                ImageColumn::make('logo_path')
                    ->label('Logótipo')
                    ->disk('public') // <-- ADICIONADO
                    ->circular(), // Imagem redonda

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Produtos')
                    ->counts('products')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Atualização')
                    ->dateTime('d/m/Y H:i')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                TernaryFilter::make('products')
                    ->label('Produtos')
                    ->boolean()
                    ->trueLabel('Apenas com produtos')
                    ->falseLabel('Apenas sem produtos')
                    ->queries(
                        true: fn(Builder $query) => $query->has('products'),
                        false: fn(Builder $query) => $query->doesntHave('products'),
                    )
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('products');
    }
}
