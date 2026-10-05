<?php

use App\Http\Controllers\Web\ActionMeasureController;
use App\Http\Controllers\Web\ChallengeController;
use App\Http\Controllers\Web\ClusterController;
use App\Http\Controllers\Web\CompanyController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DigestController;
use App\Http\Controllers\Web\IntelligenceController;
use App\Http\Controllers\Web\ProcessController;
use App\Http\Controllers\Web\RecommendationController;
use App\Http\Controllers\Web\SignalController;
use App\Http\Controllers\Web\SignalImportController;
use App\Http\Controllers\Web\SourceController;
use App\Http\Controllers\Web\TrendController;
use App\Http\Controllers\Web\UserIntelligenceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/signals', [SignalController::class, 'index'])->name('signals.index');
Route::get('/signals/new', [SignalController::class, 'create'])->name('signals.create');
Route::get('/signals/{signal}', [SignalController::class, 'show'])->name('signals.show');
Route::post('/signals', [SignalController::class, 'store'])->name('signals.store');

Route::get('/sources', [SourceController::class, 'index'])->name('sources.index');
Route::get('/sources/new', [SourceController::class, 'create'])->name('sources.create');
Route::post('/sources', [SourceController::class, 'store'])->name('sources.store');
Route::get('/sources/{source}', [SourceController::class, 'show'])->name('sources.show');
Route::get('/sources/{source}/edit', [SourceController::class, 'edit'])->name('sources.edit');
Route::get('/sources/{source}/import', [SignalImportController::class, 'create'])->name('sources.import');
Route::post('/sources/{source}/import', [SignalImportController::class, 'store'])->name('sources.import.store');
Route::get('/sources/{source}/export', [SignalImportController::class, 'export'])->name('sources.export');
Route::patch('/sources/{source}', [SourceController::class, 'update'])->name('sources.update');

Route::get('/measures', [ActionMeasureController::class, 'index'])->name('measures.index');
Route::get('/measures/new', [ActionMeasureController::class, 'create'])->name('measures.create');
Route::post('/measures', [ActionMeasureController::class, 'store'])->name('measures.store');
Route::get('/measures/{measure}', [ActionMeasureController::class, 'show'])->name('measures.show');
Route::get('/measures/{measure}/edit', [ActionMeasureController::class, 'edit'])->name('measures.edit');
Route::patch('/measures/{measure}', [ActionMeasureController::class, 'update'])->name('measures.update');

Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
Route::post('/companies/{company}/refresh', [CompanyController::class, 'refresh'])->name('companies.refresh');
Route::get('/clusters', [ClusterController::class, 'index'])->name('clusters.index');

Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
Route::get('/outcomes', [RecommendationController::class, 'outcomes'])->name('outcomes.index');
Route::get('/recommendations/{recommendation}', [RecommendationController::class, 'show'])->name('recommendations.show');
Route::post('/recommendations/bulk', [RecommendationController::class, 'bulkUpdate'])->name('recommendations.bulk');
Route::patch('/recommendations/{recommendation}', [RecommendationController::class, 'update'])->name('recommendations.update');
Route::get('/recommendations/{recommendation}/outcome', [RecommendationController::class, 'editOutcome'])->name('recommendations.outcome.edit');
Route::post('/recommendations/{recommendation}/outcome', [RecommendationController::class, 'outcome'])->name('recommendations.outcome');

Route::get('/trends', [TrendController::class, 'index'])->name('trends.index');
Route::get('/processes', [ProcessController::class, 'index'])->name('processes.index');
Route::post('/processes', [ProcessController::class, 'store'])->name('processes.store');
Route::post('/processes/{process}/advance', [ProcessController::class, 'advance'])->name('processes.advance');

Route::get('/users', [UserIntelligenceController::class, 'index'])->name('users.index');
Route::get('/users/{externalUserId}', [UserIntelligenceController::class, 'show'])->name('users.show');

Route::get('/digest', [DigestController::class, 'index'])->name('digest');

Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
Route::get('/challenges/{challenge}', [ChallengeController::class, 'show'])->name('challenges.show');
Route::get('/intelligence/platform/{source}', [IntelligenceController::class, 'platform'])->name('intelligence.platform');
Route::get('/intelligence/allocore', [IntelligenceController::class, 'allocore'])->name('intelligence.allocore');
Route::get('/intelligence/disavo', [IntelligenceController::class, 'disavo'])->name('intelligence.disavo');
