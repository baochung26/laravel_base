<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // CORS middleware (handled by Laravel's HandleCors middleware automatically if config/cors.php exists)
        
        // Request ID middleware (should be early in the stack)
        $middleware->append(\App\Http\Middleware\RequestIdMiddleware::class);
        
        // Query logging middleware (optional, can be enabled/disabled via config)
        if (env('LOG_ENABLE_QUERY_LOG', false)) {
            $middleware->append(\App\Http\Middleware\LogQueryMiddleware::class);
        }

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
