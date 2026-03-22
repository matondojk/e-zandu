<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingRateResource\Pages;
use App\Models\ShippingRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingRateResource extends Resource
{
    protected static ?string $model = ShippingRate::class;

    // Configuração da Navegação
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Taxas de Frete';
    protected static ?string $modelLabel = 'Taxa de Frete';
    protected static ?string $pluralModelLabel = 'Taxas de Frete';
    protected static ?string $navigationGroup = 'Logística';
    protected static ?string $slug = 'taxas-de-frete'; // URL em português

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('province_name')
                    ->label('Nome da Província')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('rate')
                    ->label('Valor do Frete (Kz)')
                    ->numeric()
                    ->required(),
                
                Forms\Components\TextInput::make('estimated_delivery_time')
                    ->label('Tempo de Entrega Estimado')
                    ->helperText('Ex: 24-48 horas úteis, 3-5 dias. Para ser usado pelo Bot de IA.')
                    ->maxLength(150)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('province_name')
                    ->label('Província')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate')
                    ->label('Taxa de Frete')
                    ->alignEnd()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('estimated_delivery_time')
                    ->label('Tempo Estimado')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // --- AÇÕES CONVERTIDAS EM BOTÕES DE ÍCONE ---
                Tables\Actions\EditAction::make()
                    ->iconButton() // Renderiza como um botão circular de ícone (Lápis)
                    ->tooltip('Editar Taxa'), // Dica de ajuda (tooltip) ao passar o mouse
                    
                Tables\Actions\DeleteAction::make()
                    ->iconButton() // Renderiza como um botão circular de ícone (Lixo)
                    ->tooltip('Apagar Taxa'), // Dica de ajuda (tooltip) ao passar o mouse
                // ---------------------------------------------
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
            'index' => Pages\ListShippingRates::route('/'),
            'create' => Pages\CreateShippingRate::route('/create'),
            'edit' => Pages\EditShippingRate::route('/{record}/edit'),
        ];
    }
}