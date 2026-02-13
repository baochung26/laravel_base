<?php

namespace App\Http\Controllers\Api\V1;

use Throwable;

class HealthController extends ApiController
{
    /**
     * Combined health endpoint (includes dependency checks).
     * Returns 200 always, but exposes overall_status (ok/degraded) in payload.
     */
    public function __invoke()
    {
        $checks = $this->dependencyChecks();
        $allOk = true;
        foreach ($checks as $c) {
            if (($c['ok'] ?? false) !== true) {
                $allOk = false;
                break;
            }
        }

        return $this->successResponse([
            'overall_status' => $allOk ? 'ok' : 'degraded',
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            'checks' => $checks,
        ], __('messages.success.ok'));
    }

    /**
     * Liveness probe: app process is up (no external dependencies).
     */
    public function live()
    {
        return $this->successResponse([
            'status' => 'ok',
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
        ], __('messages.success.ok'));
    }

    /**
     * Readiness probe: dependencies are reachable (DB + Redis).
     * Returns 200 when ready, 503 when not ready.
     */
    public function ready()
    {
        $checks = $this->dependencyChecks();
        $allOk = true;
        foreach ($checks as $c) {
            if (($c['ok'] ?? false) !== true) {
                $allOk = false;
                break;
            }
        }

        if (! $allOk) {
            return $this->errorResponse(__('messages.errors.service_unavailable'), 503, [
                'checks' => $checks,
                'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            ]);
        }

        return $this->successResponse([
            'status' => 'ready',
            'timestamp' => (new \DateTimeImmutable())->format(\DATE_ATOM),
            'checks' => $checks,
        ], __('messages.success.ok'));
    }

    /**
     * @return array<string, array{ok: bool, message?: string, duration_ms?: int|null}>
     */
    private function dependencyChecks(): array
    {
        return [
            'db' => $this->checkDb(),
            'redis' => $this->checkRedis(),
        ];
    }

    /**
     * @return array{ok: bool, message?: string, duration_ms?: int|null}
     */
    private function checkDb(): array
    {
        $start = microtime(true);

        try {
            \Illuminate\Support\Facades\DB::select('select 1');
            return [
                'ok' => true,
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }

    /**
     * @return array{ok: bool, message?: string, duration_ms?: int|null}
     */
    private function checkRedis(): array
    {
        $start = microtime(true);

        try {
            $pong = \Illuminate\Support\Facades\Redis::connection()->ping();

            $ok = is_string($pong) ? str_contains(strtoupper($pong), 'PONG') : (bool) $pong;

            return [
                'ok' => $ok,
                'message' => is_string($pong) ? $pong : null,
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            ];
        }
    }
}
