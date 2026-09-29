<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpaController;

Route::get('/', [SpaController::class, 'home'])->name('home');
Route::get('/property/{id}', [SpaController::class, 'property'])->name('property');
Route::get('/policy', [SpaController::class, 'policy'])->name('policy');
Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::fallback([SpaController::class, 'notFound']);

Route::middleware(['web', 'auth', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('control-panel')->group(function () {
    Route::get('/contract/download', function () {
        $path = 'contracts/rental-agreement.pdf';

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'Файл договора не найден');
        }

        return Storage::disk('local')->download(
            $path,
            'Договор-аренды.pdf',
            ['Content-Type' => 'application/pdf']
        );
    })->name('admin.contract.download');
});
