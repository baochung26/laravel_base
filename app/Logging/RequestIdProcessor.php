<?php

namespace App\Logging;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class RequestIdProcessor implements ProcessorInterface
{
    /**
     * Add request ID to log context.
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        // Add request ID if available
        if (app()->bound('request_id')) {
            $record->extra['request_id'] = app('request_id');
        }

        // Add correlation ID (same as request ID)
        if (app()->bound('request_id')) {
            $record->extra['correlation_id'] = app('request_id');
        }

        // Add user ID if authenticated
        if (auth()->check()) {
            $record->extra['user_id'] = auth()->id();
        }

        // Add request info
        if (request()) {
            $record->extra['method'] = request()->method();
            $record->extra['url'] = request()->fullUrl();
            $record->extra['ip'] = request()->ip();
        }

        return $record;
    }
}
