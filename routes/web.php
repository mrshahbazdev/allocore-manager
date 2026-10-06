<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::get('/sso/{provider}', [SsoController::class, 'login'])->name('sso');
Route::post('/ingest/{token}', [IngestController::class, 'store'])->name('ingest');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'form'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'form'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['en', 'de'])) {
        session(['locale' => $locale]);
    }

    return back();
})->name('lang');

Route::middleware('auth')->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('app');
    Route::get('/app/recommendations', [DashboardController::class, 'recommendations'])->name('recommendations');
    Route::get('/app/signals', [DashboardController::class, 'signals'])->name('signals');
    Route::get('/app/companies', [DashboardController::class, 'companies'])->name('companies');
    Route::post('/recommendations/{recommendation}/outcome', [OutcomeController::class, 'store'])->name('outcome');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
