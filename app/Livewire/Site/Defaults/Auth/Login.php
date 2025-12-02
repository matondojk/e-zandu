<?php

namespace App\Livewire\Site\Defaults\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use Illuminate\Support\Str;

class Login extends Component
{
    // Estado (Login ou Registo)
    public $isRegister = false;

    // Dados de Login
    public $loginInput = ''; // Pode ser email ou telefone
    public $password = '';

    // Dados de Registo
    public $name = '';
    public $regEmail = '';
    public $regPhone = '';
    public $regPassword = '';
    public $regPassword_confirmation = '';

    // Captura o parâmetro da URL ?register=true
    public $queryString = ['register' => ['except' => false]];

    public function mount()
    {
        // Se o utilizador já estiver logado, redirecionar
        if (Auth::check()) {
            $this->redirectUser();
        }

        // Se na URL vier ?register=true ou 1, ativa o modo registo
        if (request()->query('register')) {
            $this->isRegister = true;
        }
    }

    public function toggleMode()
    {
        $this->isRegister = !$this->isRegister;
        $this->resetValidation();
        $this->reset(['password', 'regPassword', 'regPassword_confirmation']);
    }

    public function login()
    {
        $this->validate([
            'loginInput' => 'required',
            'password' => 'required',
        ], [
            'loginInput.required' => 'Informe o seu Email ou WhatsApp.',
            'password.required' => 'Informe a sua senha.'
        ]);

        // Determinar se é email ou telefone
        $fieldType = filter_var($this->loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $this->loginInput, 'password' => $this->password])) {
            session()->regenerate();
            $this->redirectUser();
        } else {
            $this->addError('loginInput', 'Credenciais incorretas. Verifique seus dados.');
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|min:3',
            'regEmail' => 'required|email|unique:users,email',
            'regPhone' => 'required|unique:users,phone',
            'regPassword' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'O nome é obrigatório.',
            'regEmail.unique' => 'Este email já está registado.',
            'regPhone.unique' => 'Este WhatsApp já está registado.',
            'regPassword.confirmed' => 'As senhas não coincidem.',
            'regPassword.min' => 'A senha deve ter pelo menos 6 caracteres.'
        ]);

        // Criação do utilizador
        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $this->name,
            'email' => $this->regEmail,
            'phone' => $this->regPhone,
            'password' => Hash::make($this->regPassword),
            'role' => 'customer', // Role padrão
        ]);

        // Login automático
        Auth::login($user);

        $this->redirectUser();
    }

    public function redirectUser()
    {
        // Se existir carrinho na sessão (vindo do Checkout.php), vai para checkout
        // Baseado no arquivo Checkout.php fornecido, a sessão chama-se 'cart'
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            return redirect()->route('checkout');
        }

        // Caso contrário, vai para a conta do utilizador
        return redirect()->route('my-account'); // Certifique-se que esta rota existe
    }

    public function render()
    {
        return view('livewire.site.defaults.auth.login')->layout('components.layouts.defaults.app', [
            'title' => $this->isRegister ? 'Criar Conta | E-Zandu' : 'Entrar | E-Zandu'
        ]);
    }
}