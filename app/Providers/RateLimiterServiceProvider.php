<?php

namespace app\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimiterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    public function boot(): void
    {
        // Строгий лимит для создания заявок (защита от спама)
        // 5 заявок в час с одного IP
        RateLimiter::for('booking-requests', function (Request $request) {
            return Limit::perHour(5)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Слишком много заявок. Пожалуйста, свяжитесь с нами по телефону или попробуйте позже.',
                    ], 429);
                });
        });

        // Средний лимит для расчёта цен
        // 30 запросов в минуту с одного IP
        RateLimiter::for('price-calculations', function (Request $request) {
            return Limit::perMinute(30)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Слишком много запросов. Подождите немного.',
                    ], 429);
                });
        });

        // Мягкий лимит для просмотра объектов
        // 120 запросов в минуту с одного IP (защита от скрейпинга)
        RateLimiter::for('property-views', function (Request $request) {
            return Limit::perMinute(120)
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'message' => 'Слишком много запросов. Подождите немного.',
                    ], 429);
                });
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinutes(5, 200)
                ->by($request->ip())
                ->response(function () {
                    return response('Слишком много попыток входа. Подождите 10 минут.', 429);
                });
        });
    }
}
