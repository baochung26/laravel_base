<?php

namespace App\Exceptions;

use Exception;

class ResourceNotFoundException extends Exception
{
    protected $code = 404;

    public function __construct(string $message = 'Resource not found', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
