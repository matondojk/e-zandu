<?php

namespace App\Livewire\Site\Defaults;

use Livewire\Component;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class Contact extends Component
{
    public $name;
    public $email;
    public $phone;
    public $subject;
    public $message;

    public $successMessage = '';

    protected $rules = [
        'name' => 'required|min:3',
        'email' => 'required|email',
        'phone' => 'nullable|min:9',
        'subject' => 'required|min:3',
        'message' => 'required|min:10',
    ];

    protected $messages = [
        'name.required' => 'Por favor, informe o seu nome.',
        'email.required' => 'Precisamos do seu email para responder.',
        'email.email' => 'Informe um email válido.',
        'subject.required' => 'Qual é o motivo do contacto?',
        'message.required' => 'Escreva a sua mensagem.',
        'message.min' => 'A mensagem deve ser mais detalhada.',
    ];

    public function submit()
    {
        $this->validate();

        // Buscar configurações para saber para onde enviar
        $settings = Setting::first();
        
        // Email de destino (fallback para admin se não houver no banco)
        $destinatario = $settings->support_email ?? 'admin@e-zandu.com';

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
        ];

        try {
            Mail::to($destinatario)->send(new ContactFormMail($data));
            
            $this->successMessage = 'Mensagem enviada com sucesso! Entraremos em contacto em breve.';
            $this->reset(['name', 'email', 'phone', 'subject', 'message']);
            
        } catch (\Exception $e) {
            $this->addError('email', 'Ocorreu um erro ao enviar o email. Tente novamente mais tarde.');
        }
    }

    public function render()
    {
        return view('livewire.site.defaults.contact', [
            'settings' => Setting::first()
        ])->layout('components.layouts.defaults.app', [
            'title' => 'Fale Conosco | Zandu'
        ]);
    }
}