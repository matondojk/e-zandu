<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissaoResource\Pages;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PermissaoResource extends Resource
{
    protected static ?string $model = Permission::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $modelLabel = 'Permissão';
    protected static ?string $pluralModelLabel = 'Permissões';
    protected static ?string $navigationGroup = 'Administração'; // ⬅️ Grupo
    protected static ?string $slug = 'permissoes'; // ⬅️ Slug em PT

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome da Permissão')
                    ->helperText('Ex: Visualizar Vendas, Criar Produto')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug de Rota')
                    ->helperText('O slug usado no código. Ex: admin/vendas:view')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('group_name')
                    ->label('Grupo')
                    ->helperText('Ex: Vendas, Estoque, Marketing')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug (admin/rota:ação)')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group_name')
                    ->label('Grupo')
                    ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('roles.count')
                    ->label('Atribuída a')
                    ->counts('roles'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissaos::route('/'),
            'create' => Pages\CreatePermissao::route('/criar'),
            'edit' => Pages\EditPermissao::route('/{record}/editar'),
        ];
    }
}