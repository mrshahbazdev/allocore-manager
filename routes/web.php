<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OutcomeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'form'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('app');
    Route::post('/recommendations/{recommendation}/outcome', [OutcomeController::class, 'store'])->name('outcome');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
