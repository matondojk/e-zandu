<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class StockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $pdfPath;
    public int $productCount;
    public int $minimumStock;

    /**
     * Cria uma nova instância da mensagem.
     */
    public function __construct(string $pdfPath, int $productCount, int $minimumStock)
    {
        $this->pdfPath = $pdfPath;
        $this->productCount = $productCount;
        $this->minimumStock = $minimumStock;
    }

    /**
     * Define o envelope da mensagem.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "⚠️ Alerta de Stock Baixo ({$this->productCount} Produtos)",
        );
    }

    /**
     * Define o conteúdo da mensagem.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.stock.alert', // Vamos criar esta view
            with: [
                'productCount' => $this->productCount,
                'minimumStock' => $this->minimumStock,
            ],
        );
    }

    /**
     * Define os anexos da mensagem.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Relatorio_Stock_Baixo.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
