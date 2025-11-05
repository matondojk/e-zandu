<?php

namespace App\Mail;

use App\Models\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Attachment;

class OrderPaid extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $pdfPath;

    /**
     * Create a new message instance.
     */
    public function __construct(Cart $order, string $pdfAbsolutePath)
    {
        $this->order = $order;
        $this->pdfPath = $pdfAbsolutePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pagamento Confirmado - Pedido #' . $this->order->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // ✅ ALTERADO: Aponta para o template Markdown
            markdown: 'emails.orders.paid', 
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // A lógica de anexo permanece a mesma
        return [
            Attachment::fromPath($this->pdfPath)
                ->as('Fatura-Pedido-' . $this->order->id . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}