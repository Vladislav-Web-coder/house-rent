<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Приложение работает за обратным прокси (nginx в docker, поверх — Caddy на хосте).
        // Без доверенных прокси Laravel считает IP клиента адресом docker-шлюза:
        // throttle (например ical-export) и rate limit сессий «видят» одного нарушителя
        // вместо реальных клиентов. Значение задаётся в .env (TRUSTED_PROXIES=* для docker-сети).
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*'),
            headers: Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
                | Illuminate\Http\Request::HEADER_X_FORWARDED_HOST
                | Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Для API-запросов всегда отдаём JSON, включая fallback-маршрут:
        // несуществующий /api/... путь должен возвращать 404 в JSON,
        // а не HTML-страницу SPA (иначе мониторинг и клиенты ломаются).
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Not Found'], 404);
            }
        });
    })->create();
