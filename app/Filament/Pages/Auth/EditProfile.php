<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Forms\Components\Section;
use Illuminate\Support\Facades\Hash; // ✅ Importar o Hash

class EditProfile extends BaseEditProfile
{
    // Sobrescrever Título e Slug, mantendo o padrão do Filament
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
                        // 1. Avatar (Personalizado)
                        FileUpload::make('avatar')
                            ->label('Avatar')
                            ->disk('public')
                            ->directory('avatars')
                            ->image()
                            ->avatar()
                            ->columnSpanFull(), // Ocupa toda a linha

                        // 2. Nome (Componente Base do Filament)
                        $this->getNameFormComponent()
                            ->label('Nome Completo'), // Personalizamos a label

                        // 3. Email (Componente Base do Filament)
                        $this->getEmailFormComponent(),
                        
                        // 4. Telefone (Personalizado)
                        TextInput::make('phone')
                            ->label('Telefone (WhatsApp)')
                            ->tel()
                            // Garante que o telefone é único, ignorando o registo atual
                            ->unique(table: 'users', ignoreRecord: true)
                            ->nullable()
                            ->maxLength(255),
                            
                        // 5. Morada (Personalizado)
                        Textarea::make('delivery_address')
                            ->label('Morada de Entrega (Opcional)')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
                
                // --- Secção 2: Atualizar Palavra-passe (CORRIGIDO) ---
                Section::make('Atualizar Palavra-passe')
                    ->description('Deixe os campos Nova Palavra-passe e Confirmação vazios para manter a palavra-passe atual.')
                    ->columns(1)
                    ->schema([
                        // ✅ CORREÇÃO 1: Palavra-passe Atual (Campo obrigatório para qualquer alteração)
                        TextInput::make('current_password')
                            ->label('Palavra-passe Atual')
                            ->password()
                            ->required()
                            ->currentPassword()
                            ->autocomplete('current-password'),
                        
                        // ✅ CORREÇÃO 2: Nova Palavra-passe (Campo 'password' padrão do Filament)
                        TextInput::make('password')
                            ->label('Nova Palavra-passe')
                            ->password()
                            ->autocomplete('new-password')
                            // Hash apenas se o campo for preenchido (essencial)
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            // Apenas obrigatório se a 'password_confirmation' for preenchida
                            ->required(fn (string $operation): bool => filled(request()->input('data.password_confirmation')))
                            // Só atualiza a DB se o campo for preenchido
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->same('password_confirmation')
                            ->maxLength(255),
                        
                        // ✅ CORREÇÃO 3: Confirmação da Palavra-passe
                        TextInput::make('password_confirmation')
                            ->label('Confirmar Nova Palavra-passe')
                            ->password()
                            ->autocomplete('new-password')
                            ->required(fn (string $operation): bool => filled(request()->input('data.password')))
                            ->dehydrated(false), // Não precisa de salvar na DB
                    ]),
            ])
            ->model($this->getUser()); 
    }

    // Redireciona para a própria página após a edição
    protected function getRedirectUrl(): string
    {
        return static::getUrl();
    }
}
