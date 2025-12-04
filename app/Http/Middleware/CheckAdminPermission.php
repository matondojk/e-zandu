<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redirect; 
use Filament\Notifications\Notification; 
use Illuminate\Support\Facades\Route;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // ... (Verificações básicas)
        if (!$user || $user->role === 'customer') {
            return $next($request); 
        }

        if ($user->hasPermissionTo('full-access')) {
            return $next($request);
        }

        // --- Lógica de Mapeamento de Permissão ---
        $resourceSlug = $request->segment(2); 
        $actionSegment = $request->segment(3); 
        
        // 🎯 1. VERIFICAÇÃO DE EXCLUSÃO DE DASHBOARD (CORRIGIDA)
        // Se a rota atual for o destino do redirecionamento (o dashboard), PERMITE SEM VERIFICAR PERMISSÃO.
        // Isso quebra o loop.
        if (
            // Verifica se é a página raiz do painel (/admin)
            $request->segment(2) === null || 
            // Verifica se é a página Analytics Dashboard (o destino do redirect)
            $resourceSlug === 'painel-de-controle' 
        ) {
            return $next($request);
        }
        
        // ... (Restante da lógica de permissão)
        
        $permissionAction = 'view';
        
        if ($actionSegment === 'create') {
            $permissionAction = 'create';
        } elseif ($actionSegment === 'edit') {
            $permissionAction = 'edit';
        } elseif ($actionSegment === 'delete') {
            $permissionAction = 'delete';
        } elseif ($actionSegment && !is_numeric($actionSegment) && !preg_match('/^[0-9a-f-]{36}$/i', $actionSegment)) {
             $permissionAction = 'view';
        }
        
        $requiredPermission = "admin/{$resourceSlug}:{$permissionAction}";

        // 3. Verifica a Permissão
        if ($resourceSlug && $user->hasPermissionTo($requiredPermission)) {
            return $next($request);
        }
        
        // 4. AÇÃO: Notifica e Redireciona para o destino, que agora é o 'analytics-dashboard'
        $message = "Você não tem permissão (`{$requiredPermission}`) para acessar este recurso.";

        Notification::make()
            ->title('Acesso Negado!')
            ->body($message . ' Redirecionando para o painel principal.')
            ->danger()
            ->send(); 

        // Redireciona para o Analytics Dashboard (que agora está excluído do bloqueio)
        return Redirect::route('filament.admin.pages.painel-de-controle'); 
    }
}