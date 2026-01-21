<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class BadRequestException extends Exception
{
    protected $code = Response::HTTP_BAD_REQUEST;

    public function __construct(string $message = 'Bad Request', int $code = Response::HTTP_BAD_REQUEST, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
