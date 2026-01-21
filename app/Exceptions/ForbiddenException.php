<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ForbiddenException extends Exception
{
    protected $code = Response::HTTP_FORBIDDEN;

    public function __construct(string $message = 'Forbidden', int $code = Response::HTTP_FORBIDDEN, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
