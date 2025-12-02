<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Get;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Editar Meu Perfil';
    protected static ?string $slug = 'profile';
    protected static bool $shouldRegisterNavigation = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- Secção 1: Informações de Perfil ---
                Section::make('Informações de Perfil')
                    ->columns(2)
                    ->schema([
                        // 1. Avatar
                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->avatar()
                            ->columnSpanFull(),

                        // 2. Nome
                        $this->getNameFormComponent()
                            ->label('Nome Completo'),

                        // 3. Email (Bloqueado para edição)
                        $this->getEmailFormComponent()
                            ->disabled() // Não permite editar
                            ->dehydrated(false), // Não envia para o update no banco

                        // 4. Telefone
                        TextInput::make('phone')
                            ->label('Telefone (WhatsApp)')
                            ->tel()
                            ->unique(table: 'users', ignoreRecord: true)
                            ->nullable()
                            ->maxLength(255),
                    ]),

                // --- Secção 2: Atualizar Palavra-passe ---
                Section::make('Atualizar Palavra-passe')
                    ->description('Preencha apenas se desejar alterar a sua senha.')
                    ->columns(1)
                    ->schema([
                        
                        // 1. Nova Palavra-passe (Opcional)
                        TextInput::make('password')
                            ->label('Nova Palavra-passe')
                            ->password()
                            ->autocomplete('new-password')
                            ->nullable()
                            // Hash apenas se preenchido
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            // Só envia para o banco se tiver valor
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->same('password_confirmation')
                            ->maxLength(255),

                        // 2. Confirmar Nova Palavra-passe
                        TextInput::make('password_confirmation')
                            ->label('Confirmar Nova Palavra-passe')
                            ->password()
                            ->autocomplete('new-password')
                            // Obrigatório apenas se a 'password' for preenchida
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->dehydrated(false),

                        // 3. Palavra-passe Atual (Obrigatória SÓ se mudar a senha)
                        TextInput::make('current_password')
                            ->label('Palavra-passe Atual')
                            ->password()
                            ->autocomplete('current-password')
                            // A Mágica: Só é required se o campo 'password' (nova senha) estiver preenchido
                            ->required(fn (Get $get): bool => filled($get('password')))
                            ->currentPassword()
                            // Ocultar se o campo nova senha estiver vazio (opcional, mas fica mais limpo visualmente deixar visivel ou usar a logica abaixo)
                            ->visible(true), 
                    ]),
            ])
            ->model($this->getUser());
    }

    protected function getRedirectUrl(): string
    {
        return static::getUrl();
    }
}