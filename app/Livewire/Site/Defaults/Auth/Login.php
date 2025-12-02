<?php

namespace App\Livewire\Site\Defaults\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route; // Importante
use App\Models\User;
use Illuminate\Support\Str;

class Login extends Component
{
    // Estado da Visualização: 'login', 'register', 'forgot', 'reset'
    public $viewState = 'login';

    // Dados de Login
    public $loginInput = ''; 
    public $password = '';

    // Dados de Registo
    public $name = '';
    public $regEmail = '';
    public $regPhone = '';
    public $regPassword = '';
    public $regPassword_confirmation = '';

    // Dados de Recuperação (Forgot)
    public $forgotEmail = '';
    public $forgotStatus = null;

    // Dados de Redefinição (Reset - Passo final vindo do email)
    public $token = '';
    public $resetEmail = '';
    public $resetPassword = '';
    public $resetPassword_confirmation = '';
    public $resetStatus = null;

    public function mount($token = null)
    {
        if (Auth::check()) {
            $this->redirectUser();
        }

        // Determina o estado baseado na Rota atual
        $route = Route::currentRouteName();

        if ($route === 'register') {
            $this->viewState = 'register';
        } elseif ($route === 'password.request') {
            $this->viewState = 'forgot';
        } elseif ($route === 'password.reset') {
            $this->viewState = 'reset';
            $this->token = $token; // Captura o token da URL
            $this->resetEmail = request()->query('email'); // Captura o email da URL se houver
        } else {
            $this->viewState = 'login';
        }
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

        $fieldType = filter_var($this->loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$fieldType => $this->loginInput, 'password' => $this->password])) {
            session()->regenerate();
            $this->redirectUser();
        } else {
            $this->addError('loginInput', 'Credenciais incorretas.');
        }
    }

    public function register()
    {
        $this->validate([
            'name' => 'required|min:3',
            'regEmail' => 'required|email|unique:users,email',
            'regPhone' => 'required|unique:users,phone',
            'regPassword' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'id' => (string) Str::uuid(),
            'name' => $this->name,
            'email' => $this->regEmail,
            'phone' => $this->regPhone,
            'password' => Hash::make($this->regPassword),
            'role' => 'customer',
        ]);

        Auth::login($user);
        $this->redirectUser();
    }

    // Enviar o link por email
    public function sendResetLink()
    {
        $this->validate(['forgotEmail' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink(['email' => $this->forgotEmail]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->forgotStatus = __($status);
            $this->forgotEmail = '';
        } else {
            $this->addError('forgotEmail', __($status));
        }
    }

    // Efetivar a troca de senha (Novo método)
    public function resetNewPassword()
    {
        $this->validate([
            'token' => 'required',
            'resetEmail' => 'required|email',
            'resetPassword' => 'required|min:6|confirmed',
        ], [
            'resetPassword.confirmed' => 'As senhas não coincidem.',
            'resetEmail.required' => 'O email é obrigatório.'
        ]);

        $status = Password::reset(
            [
                'email' => $this->resetEmail,
                'password' => $this->resetPassword,
                'password_confirmation' => $this->resetPassword_confirmation,
                'token' => $this->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Sucesso: Faz login e redireciona
            $user = User::where('email', $this->resetEmail)->first();
            Auth::login($user);
            $this->redirectUser();
        } else {
            $this->addError('resetEmail', __($status));
        }
    }

    public function redirectUser()
    {
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            return redirect()->route('checkout');
        }
        return redirect()->route('my-account');
    }

    public function render()
    {
        $titles = [
            'login' => 'Entrar | E-Zandu',
            'register' => 'Criar Conta | E-Zandu',
            'forgot' => 'Recuperar Senha | E-Zandu',
            'reset' => 'Redefinir Senha | E-Zandu'
        ];

        return view('livewire.site.defaults.auth.login')->layout('components.layouts.defaults.app', [
            'title' => $titles[$this->viewState] ?? 'Login'
        ]);
    }
}