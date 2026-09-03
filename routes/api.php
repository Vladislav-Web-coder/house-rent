<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========== Аутентифицированный пользователь ==========
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ========== Публичные API (с rate limiting) ==========
// Лимит: создание заявки на бронирование
Route::middleware('throttle:booking-requests')->group(function () {
    Route::post('/properties/{property}/booking-requests', [
        \App\Http\Controllers\Api\BookingRequestController::class,
        'store',
    ]);
});

// Лимит: расчёт стоимости
Route::middleware('throttle:price-calculations')->group(function () {
    Route::post('/properties/{property}/calculate-price', [
        \App\Http\Controllers\Api\PricingController::class,
        'calculate',
    ]);
});

// Лимит: просмотр объектов и календарей
Route::middleware('throttle:property-views')->group(function () {
    Route::get('/properties', [
        \App\Http\Controllers\Api\PropertyController::class,
        'index',
    ]);

    Route::get('/properties/disabled-dates', [
        \App\Http\Controllers\Api\PropertyController::class,
        'disabledDates',
    ]);

    Route::get('/properties/{property}', [
        \App\Http\Controllers\Api\PropertyDetailController::class,
        'show',
    ]);

});

Route::get('/properties/{property}/calendar/{token}.ics', [
    \App\Http\Controllers\Api\IcalController::class,
    'export',
])->name('api.ical.export');

// ========== Админские маршруты (защита через auth) ==========
// Календарь объекта — без отдельного rate limit, защита через auth
Route::get('/control-panel/properties/{property}/calendar', [
    \App\Http\Controllers\Api\PropertyCalendarController::class,
    'show',
])->middleware('auth');

// Занятые даты для админки
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/control-panel/properties/{property}/disabled-dates', [
        \App\Http\Controllers\Admin\PropertyCalendarController::class,
        'disabledDates',
    ]);
});
