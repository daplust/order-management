<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        // Force Accept header for all API requests
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }
        
        $response = $next($request);
        
        // Force JSON response for API routes
        if ($request->is('api/*')) {
            $response->headers->set('Content-Type', 'application/json');
        }
        
        return $response;
    }
}