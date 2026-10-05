<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::get('/sso/{provider}', [SsoController::class, 'login'])->name('sso');
Route::post('/ingest/{token}', [IngestController::class, 'store'])->name('ingest');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'form'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'de'])) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang');

Route::middleware('auth')->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('app');
    Route::post('/recommendations/{recommendation}/outcome', [OutcomeController::class, 'store'])->name('outcome');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
