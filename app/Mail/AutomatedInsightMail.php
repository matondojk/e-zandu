<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment; // ✅ Importar Attachment

class AutomatedInsightMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $analysisText;
    public string $subjectText;
    public ?string $attachmentPath; // ✅ Adicionar caminho do anexo

    /**
     * @param string $subjectText
     * @param string $analysisText
     * @param string|null $attachmentPath Caminho completo para o ficheiro a anexar
     */
    public function __construct(string $subjectText, string $analysisText, ?string $attachmentPath = null)
    {
        $this->subjectText = $subjectText;
        $this->analysisText = $analysisText;
        $this->attachmentPath = $attachmentPath; // ✅ Atribuir
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectText,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.insights.summary',
            with: ['analysisText' => $this->analysisText],
        );
    }

    /**
     * ✅ Atualizar attachments()
     */
    public function attachments(): array
    {
        // Se um caminho de anexo foi fornecido E o ficheiro existe, anexa-o.
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            return [
                Attachment::fromPath($this->attachmentPath),
            ];
        }
        
        return [];
    }
}