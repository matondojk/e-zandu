<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRestocked extends Notification implements ShouldQueue
{
    use Queueable;

    public Product $product;
    
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $slug = $this->product->slug ?? $this->product->id;
        $url = url('/produto/' . $slug); 
        $customerName = $notifiable->name ?? 'Cliente';
        
        return (new MailMessage)
                    ->subject('🎉 Produto Reposto: ' . $this->product->name)
                    ->greeting("Olá, {$customerName}!")
                    ->line('Temos excelentes notícias! O produto **' . $this->product->name . '** que você estava à espera foi reposto no nosso estoque e está disponível para compra imediata.')
                    ->action('Comprar Agora', $url)
                    ->line('Não perca tempo! O estoque pode acabar rapidamente.')
                    ->line('Obrigado por escolher a nossa loja!');
    }
}