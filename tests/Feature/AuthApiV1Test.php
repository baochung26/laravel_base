<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthApiV1Test extends TestCase
{
    use RefreshDatabase;

    public function test_register_success_returns_user_and_token(): void
    {
        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/v1/register', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'User registered successfully',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
                'access_token',
                'refresh_token',
            ],
        ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_login_success_returns_user_token_roles_permissions(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Login successful',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
                'access_token',
                'refresh_token',
                'permissions',
                'roles',
            ],
        ]);
    }

    public function test_login_invalid_password_returns_422_with_standard_error_format(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The provided credentials are incorrect.',
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'errors' => [
                'email',
            ],
        ]);
    }

    public function test_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/me');
        $response->assertStatus(401);
    }

    public function test_me_with_bearer_token_returns_user_context(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'permissions',
                'roles',
            ],
        ]);
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_google_login_creates_user_and_returns_token(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'iss' => 'https://accounts.google.com',
                'sub' => 'google-sub-001',
                'aud' => 'test-client-id',
                'email' => 'google-user@example.com',
                'email_verified' => 'true',
                'name' => 'Google User',
                'picture' => 'https://example.com/avatar.jpg',
                'exp' => (string) (time() + 3600),
            ], 200),
        ]);

        config()->set('services.google.client_id', 'test-client-id');

        $response = $this->postJson('/api/v1/login/google', [
            'id_token' => 'valid-google-id-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Google login successful',
        ]);
        $response->assertJsonPath('data.login_type', 'google');
        $response->assertJsonStructure([
            'success',
            'message',
            'meta',
            'data' => [
                'user',
                'token',
                'access_token',
                'refresh_token',
                'permissions',
                'roles',
                'login_type',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'google-user@example.com',
            'google_id' => 'google-sub-001',
        ]);
    }

    public function test_google_login_rejects_invalid_token(): void
    {
        Http::fake([
            'https://oauth2.googleapis.com/tokeninfo*' => Http::response([
                'error' => 'invalid_token',
            ], 400),
        ]);

        $response = $this->postJson('/api/v1/login/google', [
            'id_token' => 'invalid-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid Google token.',
        ]);
    }
}
