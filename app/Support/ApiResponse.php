<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        ?array $meta = null,
        ?array $additional = null
    ): JsonResponse {
        return self::makeResponse(true, $message, $statusCode, $data, null, $meta, $additional);
    }

    public static function error(
        string $message = 'Error',
        int $statusCode = 400,
        ?array $errors = null,
        ?array $meta = null,
        ?array $additional = null
    ): JsonResponse {
        return self::makeResponse(false, $message, $statusCode, null, $errors, $meta, $additional);
    }

    private static function makeResponse(
        bool $success,
        string $message,
        int $statusCode,
        mixed $data = null,
        ?array $errors = null,
        ?array $meta = null,
        ?array $additional = null
    ): JsonResponse {
        $requestId = app()->bound('request_id') ? app('request_id') : null;

        $response = [
            'success' => $success,
            'message' => $message,
            'meta' => array_merge([
                'request_id' => $requestId,
                'timestamp' => now()->toIso8601String(),
            ], $meta ?? []),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if (config('app.debug')) {
            $response['meta']['status_code'] = $statusCode;
        }

        if ($additional !== null) {
            if (isset($additional['meta']) && is_array($additional['meta'])) {
                $response['meta'] = array_merge($response['meta'], $additional['meta']);
                unset($additional['meta']);
            }

            $response = array_merge($response, $additional);
        }

        $jsonResponse = response()->json($response, $statusCode);

        if ($requestId) {
            $jsonResponse->headers->set('X-Request-ID', $requestId);
            $jsonResponse->headers->set('X-Correlation-ID', $requestId);
        }

        return $jsonResponse;
    }
}
