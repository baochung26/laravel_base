<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;
use Monolog\Logger;
use Illuminate\Log\Logger as IlluminateLogger;

class JsonFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(IlluminateLogger|Logger $logger): void
    {
        $monolog = $logger instanceof IlluminateLogger ? $logger->getLogger() : $logger;

        // Add Request ID processor
        $monolog->pushProcessor(new RequestIdProcessor());

        foreach ($monolog->getHandlers() as $handler) {
            $handler->setFormatter(new MonologJsonFormatter());
        }
    }
}
