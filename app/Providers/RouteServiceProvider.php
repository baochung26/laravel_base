<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your rate limiters for the application.
     */
    public function boot(): void
    {
        // Global API Rate Limiting
        // Different limits for authenticated vs unauthenticated users
        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                // Authenticated users: 100 requests per minute per user
                return Limit::perMinute((int) config('app.api_rate_limit_authenticated', 100))
                    ->by($request->user()->id);
            }
            
            // Unauthenticated users: 60 requests per minute per IP
            return Limit::perMinute((int) config('app.api_rate_limit_guest', 60))
                ->by($request->ip());
        });

        // Login Rate Limiting (5 attempts per 5 minutes)
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinutes(5, 5)->by($email . '|' . $request->ip());
        });

        // Register Rate Limiting (3 attempts per 10 minutes)
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinutes(10, 3)->by($request->ip());
        });

        // Password Reset Rate Limiting (5 attempts per 15 minutes per email)
        RateLimiter::for('password-reset', function (Request $request) {
            $email = (string) ($request->email ?? $request->input('email', $request->ip()));
            return Limit::perMinutes(15, 5)->by('password-reset:' . $email . '|' . $request->ip());
        });

        // Public API Rate Limiting (for health check, etc. - more lenient)
        RateLimiter::for('api-public', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        // Register API versioning routes
        $this->routes(function () {
            // API V1 Routes
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api/v1/routes.php'));
        });
    }
}
