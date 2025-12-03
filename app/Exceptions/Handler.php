<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\Redirect;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session; // ⬅️ Adicionado

class Handler extends ExceptionHandler
{
    // ... (restante do código)

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // 🎯 1. Verifica se é uma exceção de Acesso Negado (403)
        if ($e instanceof AccessDeniedHttpException || ($e instanceof HttpException && $e->getStatusCode() === 403)) {
            
            // Verifica se a requisição está vindo do painel admin e o usuário está autenticado
            if (Route::has('filament.admin.pages.dashboard') && $request->routeIs('filament.*') && auth()->check()) {
                
                $dashboardRoute = route('filament.admin.pages.dashboard');
                $errorMessage = $e->getMessage();
                
                // --- NOVA ESTRATÉGIA: Redirecionamento Imediato com Notificação Manual ---
                
                // 1. Cria a notificação (mas não a envia diretamente, apenas a estrutura)
                $notification = Notification::make()
                    ->title('Acesso Negado!')
                    ->body($errorMessage . " Redirecionando para o painel principal.")
                    ->danger();

                // 2. Transforma a notificação em um array persistente na sessão.
                // Isso garante que o Filament a renderize no próximo request.
                $notificationArray = $notification->toDatabase(); // Usa o método interno do Filament
                
                // Obtém as notificações existentes ou inicializa se não houver
                $notifications = Session::get('filament.notifications') ?? [];
                
                // Adiciona a nova notificação
                $notifications[] = $notificationArray;
                
                // Salva o array de volta na sessão
                Session::put('filament.notifications', $notifications);
                
                // 3. Retorna o redirecionamento HTTP 302
                return Redirect::to($dashboardRoute);
            }
        }

        return parent::render($request, $e);
    }
}