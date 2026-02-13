<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! $request->user()) {
            return ApiResponse::error(__('messages.errors.unauthenticated'), 401);
        }

        if (! $request->user()->can($permission)) {
            return ApiResponse::error(
                __('messages.errors.forbidden_permission'),
                403,
                [
                    'required_permission' => [$permission],
                ]
            );
        }

        return $next($request);
    }
}
