<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IngestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OutcomeController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SourceAdminController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\UserAdminController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::get('/sso/{provider}', [SsoController::class, 'login'])->name('sso');
Route::post('/ingest/{token}', [IngestController::class, 'store'])->name('ingest');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'form'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'form'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:5,1');
    Route::get('/forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'send'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:5,1');
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
    Route::get('/app/settings', [SettingsController::class, 'form'])->name('settings');
    Route::put('/app/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/app/settings/password', [SettingsController::class, 'password'])->name('settings.password');
    Route::get('/app/sources', [SourceAdminController::class, 'index'])->name('sources');
    Route::post('/app/sources', [SourceAdminController::class, 'store'])->name('sources.store');
    Route::delete('/app/sources/{source}', [SourceAdminController::class, 'destroy'])->name('sources.destroy');
    Route::get('/app/patterns', [DashboardController::class, 'patterns'])->name('patterns');
    Route::get('/app/learning', [DashboardController::class, 'learning'])->name('learning');
    Route::get('/app/users', [UserAdminController::class, 'index'])->name('users');
    Route::put('/app/users/{user}', [UserAdminController::class, 'update'])->name('users.update');
    Route::post('/recommendations/{recommendation}/outcome', [OutcomeController::class, 'store'])->name('outcome');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
