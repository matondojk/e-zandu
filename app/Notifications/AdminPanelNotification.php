<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification; // Importar notificação do Filament

class AdminPanelNotification extends Notification
{
    use Queueable;

    private string $message;
    private string $url;
    private string $icon;

    /**
     * Criar uma nova instância de notificação.
     */
    public function __construct(string $message, string $url, string $icon = 'heroicon-o-bell')
    {
        $this->message = $message;
        $this->url = $url;
        $this->icon = $icon;
    }

    /**
     * Definir os canais de entrega (apenas base de dados).
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Armazena na base de dados para o Filament ler
    }

    /**
     * Obter a representação para o Filament.
     */
    public function toDatabase(object $notifiable)
    {
        FilamentNotification::make()
            ->title('Notificação da Loja')
            ->body($this->message)
            ->color('success')
            ->sendToDatabase($notifiable); 

        return [
            'title' => 'Notificação da Loja',
            'message' => $this->message,
            'color' => 'success',
        ];
    }

    /**
     * Obter a representação para a base de dados (necessário).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
            'icon' => $this->icon,
        ];
    }
}
