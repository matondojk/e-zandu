<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash; // Importar Hash
use Filament\Forms\Components\Section; // Importar Section

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    // --- Configuração da Navegação ---
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Administradores';
    protected static ?string $modelLabel = 'Administrador';
    protected static ?string $pluralModelLabel = 'Administradores';
    protected static ?int $navigationSort = 90;

    protected static ?string $slug = 'administradores';

    /**
     * ✅ PONTO-CHAVE 1: Filtrar para mostrar apenas 'admin'
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informações Principais')
                    ->collapsible()
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->disk('public') // Usar o disco 'public'
                            ->directory('avatars') // Salvar em 'storage/app/public/avatars'
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
                            ->unique(table: User::class, column: 'email', ignoreRecord: true) // Ignora o próprio registo na edição
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone (WhatsApp)')
                            ->tel()
                            ->unique(table: User::class, column: 'phone', ignoreRecord: true) // Ignora o próprio registo
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Palavra-passe')
                            ->password()
                            // ✅ PONTO-CHAVE 2: Lógica de Password
                            ->required(fn(string $context): bool => $context === 'create') // Obrigatório apenas ao Criar
                            ->nullable(fn(string $context): bool => $context === 'edit') // Opcional ao Editar
                            ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null) // Só faz hash se for preenchido
                            ->dehydrated(fn(?string $state): bool => filled($state)) // Só envia para o update se for preenchido
                            ->maxLength(255),

                        Forms\Components\Textarea::make('delivery_address')
                            ->label('Morada (Opcional)')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->disk('public') // Ler do disco 'public'
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data de Registo')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'), // Página de View que gerámos
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
