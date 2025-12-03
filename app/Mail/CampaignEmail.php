<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Propriedades públicas que serão passadas para a view automaticamente.
     * Renomeamos $subject para $campaignSubject para evitar conflito com a classe Mailable.
     */
    public function __construct(
        public string $campaignSubject, // 🚨 CORRIGIDO: Nome da propriedade alterado
        public string $body,
    ) {
        // Agora podemos usar a propriedade nativa do Mailable para definir o assunto
        // (O Laravel/PHP fará isto se envelope() for chamado)
    }

    /**
     * Define o assunto (subject) do e-mail.
     * Usamos a nova propriedade $this->campaignSubject.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->campaignSubject, // Usa a nova propriedade
        );
    }

    /**
     * Define o conteúdo e a view.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.campaign.template', // Usa a view X-Mail/Markdown
            with: [
                'body' => $this->body,
                // A view Blade (template.blade.php) precisa do nome 'subject' para o seu cabeçalho.
                // Passamos o valor de $campaignSubject para a variável $subject na view.
                'subject' => $this->campaignSubject, 
            ],
        );
    }
}