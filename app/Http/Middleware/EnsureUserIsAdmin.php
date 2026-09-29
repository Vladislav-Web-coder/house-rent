<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Допускает только пользователей с ролью admin.
 * Используется для защиты панели Filament и админских маршрутов:
 * сам по себе факт авторизации не означает доступ к админке.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            abort(403, 'Доступ запрещён');
        }

        return $next($request);
    }
}
