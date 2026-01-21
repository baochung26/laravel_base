<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends Exception
{
    protected $code = Response::HTTP_UNAUTHORIZED;

    public function __construct(string $message = 'Unauthorized', int $code = Response::HTTP_UNAUTHORIZED, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
