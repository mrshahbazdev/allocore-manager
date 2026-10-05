<?php

namespace App\Http\Middleware;

use App\Models\Source;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SourceTokenAuth
{
    /**
     * Authenticates data-provider platforms by their ingest token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $source = $token
            ? Source::where('ingest_token', $token)->where('is_active', true)->first()
            : null;

        if (! $source) {
            return response()->json(['message' => 'Invalid source token.'], 401);
        }

        $request->attributes->set('source', $source);

        return $next($request);
    }
}
