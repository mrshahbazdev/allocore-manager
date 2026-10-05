<?php

use App\Http\Controllers\Api\V1\IntelligenceController;
use App\Http\Controllers\Api\V1\OutcomeController;
use App\Http\Controllers\Api\V1\RecommendationController;
use App\Http\Controllers\Api\V1\SignalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Data providers push signals with their ingest token.
    Route::post('/signals', [SignalController::class, 'store'])
        ->middleware('source.token');

    // Ecosystem intelligence endpoints (Sanctum-authenticated callers).
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/companies/{company}/recommendations', [RecommendationController::class, 'index']);
        Route::post('/companies/{company}/recommendations/refresh', [RecommendationController::class, 'refresh']);
        Route::patch('/recommendations/{recommendation}', [RecommendationController::class, 'update']);
        Route::post('/recommendations/{recommendation}/outcome', [OutcomeController::class, 'store']);

        Route::get('/intelligence/company/{company}', [IntelligenceController::class, 'company']);
        Route::get('/intelligence/platform/{source}', [IntelligenceController::class, 'platform']);
        Route::get('/intelligence/allocore', [IntelligenceController::class, 'allocore']);
        Route::get('/intelligence/disavo', [IntelligenceController::class, 'disavo']);
    });
});
