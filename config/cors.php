<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS) Configuration
|--------------------------------------------------------------------------
|
| Browser security: "Access-Control-Allow-Origin: *" cannot be used with
| credentials (cookies, Authorization). So we never use wildcard + credentials.
|
| - Set CORS_ALLOWED_ORIGINS to a comma-separated list of origins (e.g. for
|   Next.js: http://localhost:3000,https://your-app.com).
| - Production: always set explicit origins; do not use *.
| - If you use Bearer token only (no cookies), you may set supports_credentials
|   to false and optionally allow * for development only.
|
*/

$corsOriginsEnv = env('CORS_ALLOWED_ORIGINS');
$allowedOrigins = $corsOriginsEnv
    ? array_map('trim', explode(',', $corsOriginsEnv))
    : (env('APP_ENV') === 'production' ? [] : ['http://localhost:3000', 'http://localhost:5173', 'http://127.0.0.1:8000']);

$isWildcard = $allowedOrigins === ['*'] || (count($allowedOrigins) === 1 && trim($allowedOrigins[0] ?? '') === '*');

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-ID', 'X-Correlation-ID'],

    'max_age' => 0,

    // Wildcard (*) + credentials is invalid in browsers; use false when origins include *
    'supports_credentials' => ! $isWildcard,

];
