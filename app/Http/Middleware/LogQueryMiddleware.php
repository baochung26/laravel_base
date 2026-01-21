<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogQueryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Enable query log
        DB::enableQueryLog();

        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Get query log
        $queries = DB::getQueryLog();

        // Get slow query threshold (default: 1000ms = 1 second)
        $slowQueryThreshold = config('logging.slow_query_threshold', 1000);

        // Log slow queries
        foreach ($queries as $query) {
            $queryTime = $query['time']; // Query time in milliseconds

            if ($queryTime > $slowQueryThreshold) {
                Log::channel('query')->warning('Slow query detected', [
                    'request_id' => app('request_id'),
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $queryTime . 'ms',
                    'threshold' => $slowQueryThreshold . 'ms',
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                ]);
            }
        }

        // Log all queries in debug mode
        if (config('app.debug') && ! empty($queries)) {
            Log::channel('query')->debug('Query log', [
                'request_id' => app('request_id'),
                'queries' => $queries,
                'total_queries' => count($queries),
                'execution_time' => round($executionTime, 2) . 'ms',
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        }

        return $response;
    }
}
