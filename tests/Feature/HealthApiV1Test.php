<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class HealthApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_ok(): void
    {
        Redis::shouldReceive('connection->ping')->andReturn('PONG');

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OK',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'overall_status',
                'timestamp',
                'checks' => [
                    'db' => ['ok'],
                    'redis' => ['ok'],
                ],
            ],
        ]);
    }

    public function test_liveness_returns_ok_without_dependencies(): void
    {
        $response = $this->getJson('/api/v1/health/live');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OK',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
            ],
        ]);
    }

    public function test_readiness_returns_ready_when_db_and_redis_ok(): void
    {
        Redis::shouldReceive('connection->ping')->andReturn('PONG');

        $response = $this->getJson('/api/v1/health/ready');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OK',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'status',
                'timestamp',
                'checks' => [
                    'db' => ['ok'],
                    'redis' => ['ok'],
                ],
            ],
        ]);
    }

    public function test_readiness_returns_503_when_redis_fails(): void
    {
        Redis::shouldReceive('connection->ping')->andThrow(new \RuntimeException('Redis down'));

        $response = $this->getJson('/api/v1/health/ready');

        $response->assertStatus(503);
        $response->assertJson([
            'success' => false,
            'message' => 'Service Unavailable',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'checks' => [
                    'db' => ['ok'],
                    'redis' => ['ok', 'message'],
                ],
                'timestamp',
            ],
        ]);
    }
}

