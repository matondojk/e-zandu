<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Notifications\Notification;

class ReadOnlyDemo
{
    public function handle($request, Closure $next)
    {
        if (config('app.demo_mode') && $request->isMethodSafe() === false) {

            Notification::make()
                ->title('Ação bloqueada')
                ->body('Modo demonstração: alterações não são permitidas.')
                ->danger()
                ->send();

            return back();
        }

        return $next($request);
    }
}
