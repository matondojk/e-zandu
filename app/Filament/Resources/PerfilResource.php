<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerfilResource\Pages;
use App\Models\Role;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class PerfilResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'Perfil';
    protected static ?string $pluralModelLabel = 'Perfis';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $slug = 'perfis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do Perfil')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->readOnly()
                    ->helperText('Gerado automaticamente pelo sistema.'),

                // Campo para associar Permissões
                Forms\Components\Select::make('permissions')
                    ->label('Permissões Atribuídas')
                    ->relationship('permissions', 'name')
                    ->getOptionLabelFromRecordUsing(fn(Permission $record) => "{$record->group_name}: {$record->name}")
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
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
                    ->label('Slug de Acesso'),
                // ❌ REMOVIDO: Tables\Columns\TextColumn::make('permissions.count')

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                // 3. ✅ AÇÃO: Edição com Permissão e Icon Button
                Tables\Actions\EditAction::make()
                    ->iconButton() // ⬅️ ICON BUTTON
                    ->visible(fn(Model $record) => Auth::user()->hasPermissionTo('admin/perfis:edit') || Auth::user()->hasPermissionTo('full-access')),

                // 4. ✅ AÇÃO: Exclusão com Permissão e Icon Button
                Tables\Actions\DeleteAction::make()
                    ->iconButton() // ⬅️ ICON BUTTON
                    ->visible(fn(Model $record) => Auth::user()->hasPermissionTo('admin/perfis:delete') || Auth::user()->hasPermissionTo('full-access')),
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
            'index' => Pages\ListPerfils::route('/'),
            'create' => Pages\CreatePerfil::route('/criar'),
            'edit' => Pages\EditPerfil::route('/{record}/editar'),
        ];
    }
}
