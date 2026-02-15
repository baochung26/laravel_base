<?php

namespace App\Http\Middleware;

use App\Support\WebRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request. Redirect non-admin users to their allowed home route.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('messages.errors.forbidden_role'),
                ], 403);
            }

            return redirect()
                ->route(WebRedirect::postAuthRouteName($user))
                ->with('error', __('messages.errors.forbidden_feature'));
        }

        return $next($request);
    }
}
