<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InternalServerErrorException extends Exception
{
    protected $code = Response::HTTP_INTERNAL_SERVER_ERROR;

    public function __construct(string $message = 'Internal Server Error', int $code = Response::HTTP_INTERNAL_SERVER_ERROR, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
