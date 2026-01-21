<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class TooManyRequestsException extends Exception
{
    protected $code = Response::HTTP_TOO_MANY_REQUESTS;

    public function __construct(string $message = 'Too Many Requests', int $code = Response::HTTP_TOO_MANY_REQUESTS, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
