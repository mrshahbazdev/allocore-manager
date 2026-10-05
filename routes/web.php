<?php

use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\IntelligenceController;
use App\Http\Controllers\Web\RecommendationController;
use App\Http\Controllers\Web\SignalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/signals/new', [SignalController::class, 'create'])->name('signals.create');
Route::post('/signals', [SignalController::class, 'store'])->name('signals.store');

Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::post('/companies/{company}/refresh', [CompanyController::class, 'refresh'])->name('companies.refresh');

Route::patch('/recommendations/{recommendation}', [RecommendationController::class, 'update'])->name('recommendations.update');
Route::post('/recommendations/{recommendation}/outcome', [RecommendationController::class, 'outcome'])->name('recommendations.outcome');

Route::get('/intelligence/platform/{source}', [IntelligenceController::class, 'platform'])->name('intelligence.platform');
Route::get('/intelligence/allocore', [IntelligenceController::class, 'allocore'])->name('intelligence.allocore');
Route::get('/intelligence/disavo', [IntelligenceController::class, 'disavo'])->name('intelligence.disavo');
