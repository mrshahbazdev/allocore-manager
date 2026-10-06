<?php

namespace App\Http\Middleware;

use App\Support\DecisionEngine;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shared hosting has no cron — run the engine opportunistically after the
 * response, throttled to roughly once per hour via a cache lock.
 */
class RunDecisionEngineHourly
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (! $request->user()) {
            return;
        }

        $lock = Cache::lock('decisions:engine-lock', 3300);
        if (! $lock->get()) {
            return;
        }

        try {
            app(DecisionEngine::class)->run();
        } catch (\Throwable) {
            // Never break the page for a background recompute.
        }
    }
}
