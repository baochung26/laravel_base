<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $config = config('security.headers');
        if (! ($config['enabled'] ?? false)) {
            return $response;
        }

        // Content Security Policy
        if (! empty($config['csp'])) {
            $response->headers->set('Content-Security-Policy', $config['csp']);
        }

        // HSTS (only set on HTTPS)
        $hsts = $config['hsts'] ?? [];
        if (($hsts['enabled'] ?? false) && $request->isSecure()) {
            $value = 'max-age=' . (int) ($hsts['max_age'] ?? 31536000);
            if (! empty($hsts['include_subdomains'])) {
                $value .= '; includeSubDomains';
            }
            if (! empty($hsts['preload'])) {
                $value .= '; preload';
            }
            $response->headers->set('Strict-Transport-Security', $value);
        }

        // Other headers
        if (! empty($config['x_frame_options'])) {
            $response->headers->set('X-Frame-Options', $config['x_frame_options']);
        }
        if (! empty($config['x_content_type_options'])) {
            $response->headers->set('X-Content-Type-Options', $config['x_content_type_options']);
        }
        if (! empty($config['referrer_policy'])) {
            $response->headers->set('Referrer-Policy', $config['referrer_policy']);
        }
        if (! empty($config['permissions_policy'])) {
            $response->headers->set('Permissions-Policy', $config['permissions_policy']);
        }

        return $response;
    }
}
