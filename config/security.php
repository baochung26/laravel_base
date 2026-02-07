<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),

        // Content Security Policy (CSP)
        // Example: "default-src 'self'; img-src 'self' data:; script-src 'self'"
        'csp' => env('SECURITY_CSP', ''),

        // HTTP Strict Transport Security (HSTS)
        'hsts' => [
            'enabled' => env('SECURITY_HSTS_ENABLED', false),
            'max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
            'include_subdomains' => (bool) env('SECURITY_HSTS_INCLUDE_SUBDOMAINS', true),
            'preload' => (bool) env('SECURITY_HSTS_PRELOAD', false),
        ],

        // Other standard headers
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'permissions_policy' => env('SECURITY_PERMISSIONS_POLICY', ''),
    ],
];
