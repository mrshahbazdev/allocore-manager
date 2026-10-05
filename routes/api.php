<?php

use App\Http\Controllers\SignalIngestController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/signals/{token}', [SignalIngestController::class, 'store']);
