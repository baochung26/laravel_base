<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request. Redirect to dashboard if user is not admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.errors.forbidden_role'),
                ], 403);
            }

            return redirect()
                ->route('dashboard')
                ->with('error', __('messages.errors.forbidden_feature'));
        }

        return $next($request);
    }
}
