<?php

namespace App\Helpers;

class RequestId
{
    /**
     * Get or generate request ID.
     */
    public static function get(): string
    {
        if (! app()->bound('request_id')) {
            app()->instance('request_id', self::generate());
        }

        return app('request_id');
    }

    /**
     * Generate a unique request ID.
     */
    public static function generate(): string
    {
        return sprintf(
            '%s-%s-%s',
            config('app.name', 'laravel'),
            date('YmdHis'),
            substr(str_replace(['-', '_'], '', \Illuminate\Support\Str::uuid()), 0, 8)
        );
    }

    /**
     * Set request ID (for correlation).
     */
    public static function set(string $requestId): void
    {
        app()->instance('request_id', $requestId);
    }
}
