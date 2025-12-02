<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MyAccount extends Component
{
    use WithFileUploads;

    // Dados do Perfil
    public $name;
    public $email; // Apenas leitura
    public $phone;
    public $delivery_address;
    
    // Upload de Avatar
    public $avatar;
    public $existingAvatar;

    // Alteração de Senha
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->delivery_address = $user->delivery_address;
        $this->existingAvatar = $user->avatar;
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|min:3',
            'phone' => ['nullable', Rule::unique('users', 'phone')->ignore($user->id)],
            'delivery_address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048', // Max 2MB
        ], [
            'phone.unique' => 'Este número de telefone já está em uso.',
            'avatar.image' => 'O ficheiro deve ser uma imagem.',
            'avatar.max' => 'A imagem não pode ter mais de 2MB.',
        ]);

        // Lógica de Upload de Avatar
        if ($this->avatar) {
            // Se já tiver avatar antigo (e não for URL externa), deletar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Salvar na pasta 'avatars' dentro do disco 'public'
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $this->name;
        $user->phone = $this->phone;
        $user->delivery_address = $this->delivery_address;
        $user->save();

        // Atualiza a pré-visualização se houve upload
        if ($this->avatar) {
            $this->existingAvatar = $user->avatar;
            $this->reset('avatar');
        }

        $this->dispatch('toast', ['type' => 'success', 'message' => 'Perfil atualizado com sucesso!']);
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ], [
            'current_password.required' => 'Informe a senha atual.',
            'new_password.min' => 'A nova senha deve ter pelo menos 6 caracteres.',
            'new_password.confirmed' => 'As senhas não coincidem.',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'A senha atual está incorreta.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password)
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        
        $this->dispatch('toast', ['type' => 'success', 'message' => 'Senha alterada com sucesso!']);
    }

    public function render()
    {
        return view('livewire.site.defaults.my-account')->layout('components.layouts.defaults.app', [
            'title' => 'Minha Conta | Zandu'
        ]);
    }
}