<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    /**
     * Handle an incoming request. Redirect to home if user is not admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! (method_exists($user, 'hasRole') && $user->hasRole('admin'))) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Forbidden. You do not have the required role.',
                ], 403);
            }

            return redirect()
                ->route('welcome')
                ->with('error', 'Bạn không có quyền truy cập tính năng này.');
        }

        return $next($request);
    }
}
