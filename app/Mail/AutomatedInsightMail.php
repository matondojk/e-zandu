<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class AutomatedInsightMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $analysisText;
    public string $subjectText;
    public ?string $attachmentPath;

    public function __construct(string $subjectText, string $analysisText, ?string $attachmentPath = null)
    {
        $this->subjectText = $subjectText;
        $this->analysisText = $analysisText;
        $this->attachmentPath = $attachmentPath;
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

    public function attachments(): array
    {
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            return [
                Attachment::fromPath($this->attachmentPath)
                    ->as('Resumo-Performance.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}