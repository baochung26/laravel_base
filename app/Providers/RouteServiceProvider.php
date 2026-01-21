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
    public const HOME = '/home';

    /**
     * Define your rate limiters for the application.
     */
    public function boot(): void
    {
        // API Rate Limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
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

        // General API throttling (100 requests per minute for authenticated users)
        RateLimiter::for('api-auth', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::perMinute(60)->by($request->ip());
        });

        // Register API versioning routes
        $this->routes(function () {
            // API V1 Routes
            Route::middleware('api')
                ->prefix('api/v1')
                ->group(base_path('routes/api/v1.php'));

            // Legacy API Routes (backward compatibility)
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        });
    }
}
