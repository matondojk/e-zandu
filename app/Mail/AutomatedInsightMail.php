<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutomatedInsightMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $analysisText;
    public string $subjectText;

    public function __construct(string $subjectText, string $analysisText)
    {
        $this->subjectText = $subjectText;
        $this->analysisText = $analysisText;
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
            markdown: 'emails.insights.summary', // Vamos criar esta view
            with: ['analysisText' => $this->analysisText],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}