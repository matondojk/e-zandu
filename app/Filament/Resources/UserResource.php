<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model; // Importar Model
use Illuminate\Support\Facades\Auth; // Importar Auth

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $navigationLabel = 'Administradores';
    protected static ?string $modelLabel = 'Administrador';
    protected static ?string $pluralModelLabel = 'Administradores';
    protected static ?string $slug = 'administradores'; // SLUG: admin/administradores

    // 🎯 REGRAS DE PERMISSÃO BÁSICA (Acesso ao Resource/Menu)
    public static function canViewAny(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasPermissionTo('admin/administradores:view') || $user->hasPermissionTo('full-access');
    }

    // 🎯 REGRAS DE PERMISSÃO DE CRIAÇÃO: Apenas Super Admin
    public static function canCreate(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('super-admin'); 
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            // ✅ CORREÇÃO DE ERRO: Removido o disabled() global do Form. 
            // A restrição será feita nos métodos canEdit e na Section de Perfis.
            ->schema([
                Section::make('Informações Principais')
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->imageEditor()
                            ->circleCropper()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nome Completo')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(table: User::class, column: 'email', ignoreRecord: true)
                            ->maxLength(255),

                        // ❌ REMOVIDO: Campo Telefone da Morada
                        
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone (WhatsApp)')
                            ->tel()
                            ->unique(table: User::class, column: 'phone', ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Palavra-passe')
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->nullable(fn(string $context): bool => $context === 'edit')
                            ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->maxLength(255),

                        // ❌ REMOVIDO: delivery_address (Morada)
                    ])->columns(2),

                Section::make('Perfis de Acesso e Permissões')
                    ->description('Defina o(s) perfil(is) que este administrador terá para o controle de acesso ao sistema.')
                    ->schema([
                        Select::make('roles')
                            ->label('Perfis Atribuídos')
                            ->relationship('roles', 'name', fn(Builder $query) => $query->where('name', '!=', 'customer'))
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Selecione um ou mais perfis.')
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('role')
                            ->default('admin')
                            ->dehydrated(true),
                    ])->columns(1)
                    // 🎯 REGRAS DE PERMISSÃO DE EDIÇÃO DE PERFIS
                    ->disabled(function () {
                        // Apenas Super Admin pode editar os perfis
                        return !Auth::user()->hasRole('super-admin');
                    }), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Perfis de Acesso')
                    ->badge()
                    ->separator(','),

                // ❌ REMOVIDO: Tables\Columns\TextColumn::make('phone')
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Filtrar por Perfil'),
            ])
            ->actions([
                // 🎯 AÇÃO 1: VISUALIZAR (Super Admin OU Gerente)
                Tables\Actions\ViewAction::make()
                    ->iconButton() // ⬅️ ICON BUTTON
                    ->visible(fn() => Auth::user()->hasRole('super-admin') || Auth::user()->hasRole('gerente')), 
                
                // 🎯 AÇÃO 2: EDITAR (APENAS Super Admin)
                Tables\Actions\EditAction::make()
                    ->iconButton() // ⬅️ ICON BUTTON
                    ->visible(fn() => Auth::user()->hasRole('super-admin')),
                
                // 🎯 AÇÃO 3: EXCLUIR (APENAS Super Admin)
                Tables\Actions\DeleteAction::make()
                    ->iconButton() // ⬅️ ICON BUTTON
                    ->visible(fn() => Auth::user()->hasRole('super-admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // 🎯 AÇÃO BULK: EXCLUIR (APENAS Super Admin)
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => Auth::user()->hasRole('super-admin')),
                ]),
            ]);
    }
    
    // 🎯 REGRAS DE PERMISSÃO PARA AS PÁGINAS (canEdit e canDelete)
    
    // Apenas Super Admin pode editar o formulário/acessar a página de edição
    public static function canEdit(Model $record): bool
    {
        return Auth::user()->hasRole('super-admin');
    }

    // Apenas Super Admin pode deletar
    public static function canDelete(Model $record): bool
    {
        return Auth::user()->hasRole('super-admin');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}