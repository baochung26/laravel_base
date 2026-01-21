<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as MonologJsonFormatter;
use Monolog\Logger;

class JsonFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        // Add Request ID processor
        $logger->pushProcessor(new RequestIdProcessor());

        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new MonologJsonFormatter());
        }
    }
}
