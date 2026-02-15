<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;

class WebRedirect
{
    /**
     * Resolve the web route name after auth checks.
     */
    public static function postAuthRouteName(?Authenticatable $user): string
    {
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return 'dashboard';
        }

        return 'profile.show';
    }

    /**
     * Resolve the web route URL after auth checks.
     */
    public static function postAuthRoute(?Authenticatable $user, bool $absolute = true): string
    {
        return route(self::postAuthRouteName($user), absolute: $absolute);
    }
}
