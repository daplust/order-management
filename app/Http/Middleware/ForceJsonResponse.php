<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Force API routes to be treated as expecting JSON and ensure responses are JSON.
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        if ($request->is('api/*')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}