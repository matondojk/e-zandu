@component('mail::message')
# 📩 Novo Contacto Recebido

Olá! Você recebeu uma nova mensagem através do formulário de contacto do site **E-Zandu**.

---

**👤 Nome:**  
{{ $data['name'] }}

**📧 Email:**  
{{ $data['email'] }}

**📱 Telefone:**  
{{ $data['phone'] ?? 'Não informado' }}

**📝 Assunto:**  
{{ $data['subject'] }}

---

## ✉️ Mensagem

@component('mail::panel')
{!! nl2br(e($data['message'])) !!}
@endcomponent

---

Obrigado,<br>
@endcomponent
