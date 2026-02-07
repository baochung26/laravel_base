<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * The list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,
        ResourceNotFoundException::class,
        UnauthorizedException::class,
        ForbiddenException::class,
        BadRequestException::class,
        TooManyRequestsException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log exceptions with Request ID
            if ($this->shouldReport($e)) {
                \Illuminate\Support\Facades\Log::error('Exception occurred', [
                    'request_id' => app()->bound('request_id') ? app('request_id') : 'N/A',
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): HttpResponse
    {
        // Handle API requests only
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions and return standardized JSON response.
     */
    protected function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Handle custom exceptions
        if ($e instanceof ValidationException) {
            return $this->renderValidationException($request, $e);
        }

        if ($e instanceof ResourceNotFoundException) {
            return $this->renderResourceNotFoundException($request, $e);
        }

        if ($e instanceof UnauthorizedException) {
            return $this->renderUnauthorizedException($request, $e);
        }

        if ($e instanceof ForbiddenException) {
            return $this->renderForbiddenException($request, $e);
        }

        if ($e instanceof BadRequestException) {
            return $this->renderBadRequestException($request, $e);
        }

        if ($e instanceof TooManyRequestsException) {
            return $this->renderTooManyRequestsException($request, $e);
        }

        if ($e instanceof InternalServerErrorException) {
            return $this->renderInternalServerErrorException($request, $e);
        }

        // Handle Laravel's validation exception
        if ($e instanceof LaravelValidationException) {
            return $this->renderLaravelValidationException($request, $e);
        }

        // Handle authentication exception
        if ($e instanceof AuthenticationException) {
            return $this->renderAuthenticationException($request, $e);
        }

        // Handle access denied exception
        if ($e instanceof AccessDeniedHttpException) {
            return $this->renderForbiddenException($request, new ForbiddenException($e->getMessage() ?: 'Forbidden'));
        }

        // Handle unauthorized HTTP exception
        if ($e instanceof UnauthorizedHttpException) {
            return $this->renderUnauthorizedException($request, new UnauthorizedException($e->getMessage() ?: 'Unauthorized'));
        }

        // Handle not found HTTP exception
        if ($e instanceof NotFoundHttpException) {
            return $this->renderResourceNotFoundException($request, new ResourceNotFoundException('Resource not found'));
        }

        // Handle method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->renderMethodNotAllowedException($request, $e);
        }

        // Handle too many requests HTTP exception
        if ($e instanceof TooManyRequestsHttpException) {
            return $this->renderTooManyRequestsException($request, new TooManyRequestsException($e->getMessage() ?: 'Too Many Requests'));
        }

        // Handle model not found
        if ($e instanceof ModelNotFoundException) {
            return $this->renderResourceNotFoundException($request, new ResourceNotFoundException('Resource not found'));
        }

        // Handle all other exceptions
        return $this->renderGenericException($request, $e);
    }

    /**
     * Render validation exception.
     */
    protected function renderValidationException(Request $request, ValidationException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Validation failed',
            $e->getCode() ?: HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
            ['general' => [$e->getMessage()]]
        );
    }

    /**
     * Render Laravel validation exception.
     */
    protected function renderLaravelValidationException(Request $request, LaravelValidationException $e): JsonResponse
    {
        $errors = $e->errors();
        $firstError = collect($errors)->flatten()->first();

        return $this->standardizedErrorResponse(
            is_string($firstError) ? $firstError : 'Validation failed',
            HttpResponse::HTTP_UNPROCESSABLE_ENTITY,
            $errors
        );
    }

    /**
     * Render resource not found exception.
     */
    protected function renderResourceNotFoundException(Request $request, ResourceNotFoundException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Resource not found',
            $e->getCode() ?: HttpResponse::HTTP_NOT_FOUND
        );
    }

    /**
     * Render unauthorized exception.
     */
    protected function renderUnauthorizedException(Request $request, UnauthorizedException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Unauthorized',
            $e->getCode() ?: HttpResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Render authentication exception.
     */
    protected function renderAuthenticationException(Request $request, AuthenticationException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Unauthenticated',
            HttpResponse::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Render forbidden exception.
     */
    protected function renderForbiddenException(Request $request, ForbiddenException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Forbidden',
            $e->getCode() ?: HttpResponse::HTTP_FORBIDDEN
        );
    }

    /**
     * Render bad request exception.
     */
    protected function renderBadRequestException(Request $request, BadRequestException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Bad Request',
            $e->getCode() ?: HttpResponse::HTTP_BAD_REQUEST
        );
    }

    /**
     * Render too many requests exception.
     */
    protected function renderTooManyRequestsException(Request $request, TooManyRequestsException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Too Many Requests',
            $e->getCode() ?: HttpResponse::HTTP_TOO_MANY_REQUESTS
        );
    }

    /**
     * Render method not allowed exception.
     */
    protected function renderMethodNotAllowedException(Request $request, MethodNotAllowedHttpException $e): JsonResponse
    {
        $allowedMethods = $e->getHeaders()['Allow'] ?? '';

        return $this->standardizedErrorResponse(
            'Method not allowed. Allowed methods: ' . $allowedMethods,
            HttpResponse::HTTP_METHOD_NOT_ALLOWED
        );
    }

    /**
     * Render internal server error exception.
     */
    protected function renderInternalServerErrorException(Request $request, InternalServerErrorException $e): JsonResponse
    {
        return $this->standardizedErrorResponse(
            $e->getMessage() ?: 'Internal Server Error',
            $e->getCode() ?: HttpResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Render generic exception.
     */
    protected function renderGenericException(Request $request, Throwable $e): JsonResponse
    {
        $message = config('app.debug') 
            ? $e->getMessage() 
            : 'An error occurred. Please try again later.';

        $code = $e->getCode() > 0 && $e->getCode() < 600 
            ? $e->getCode() 
            : HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

        return $this->standardizedErrorResponse(
            $message,
            $code
        );
    }

    /**
     * Standardized error response format.
     */
    protected function standardizedErrorResponse(
        string $message,
        int $statusCode,
        ?array $errors = null,
        ?array $additional = null
    ): JsonResponse {
        return ApiResponse::error($message, $statusCode, $errors, $additional);
    }
}
