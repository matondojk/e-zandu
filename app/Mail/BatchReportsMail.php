<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment; // Importação essencial para o método attachments()
use App\Models\Setting; // Modelo para configurações

class BatchReportsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $body;
    public $setting; // Configurações da loja (para logo)
    public $filePaths = []; // Array para guardar os caminhos dos arquivos

    /**
     * Cria uma nova instância da mensagem.
     *
     * @param string $subject Assunto do e-mail.
     * @param string $body Corpo da mensagem.
     * @param array $filePaths Array de caminhos de arquivos PDF a anexar.
     */
    public function __construct(string $subject, string $body, array $filePaths = [])
    {
        $this->subject = $subject;
        $this->body = $body;
        $this->filePaths = $filePaths; 
        
        // Carrega as configurações da loja para uso na view.
        $this->setting = Setting::first();
    }

    /**
     * Obtém o envelope da mensagem (assunto, remetente, etc.).
     */
    public function envelope(): Envelope
    {
        // Se a configuração de e-mail da empresa existir, use-a como remetente.

        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Obtém a definição de conteúdo da mensagem.
     */
    public function content(): Content
    {
        return new Content(
            // Usa a view Markdown para o corpo do e-mail
            markdown: 'emails.batch-reports',
            with: [
                'body' => $this->body,
            ],
        );
    }

    /**
     * Obtém os anexos para a mensagem.
     * Este método é o padrão moderno do Laravel para anexos.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $mailAttachments = [];
        
        // Itera sobre o array de caminhos de arquivos PDF para anexar
        foreach ($this->filePaths as $path) {
            // Garante que o arquivo existe antes de tentar anexar
            if (file_exists($path)) {
                $mailAttachments[] = Attachment::fromPath($path);
            }
        }

        return $mailAttachments;
    }
}