<?php

namespace App\Http\Middleware;

use App\Helpers\RequestId;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestIdMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get or generate Request ID
        $requestId = $request->header('X-Request-ID') 
            ?: $request->header('X-Correlation-ID') 
            ?: RequestId::generate();

        // Set Request ID
        RequestId::set($requestId);

        // Add Request ID to response headers
        $response = $next($request);
        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-Correlation-ID', $requestId);

        return $response;
    }
}
