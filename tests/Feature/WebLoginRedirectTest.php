<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class WebLoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_login_redirects_to_home_when_no_intended_url(): void
    {
        $user = $this->createVerifiedUser([
            'email' => 'member@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('welcome'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_to_intended_authenticated_page(): void
    {
        $user = $this->createVerifiedUser([
            'email' => 'profile-user@example.com',
        ]);

        $this->get('/profile')->assertRedirect(route('login'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('profile.show'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_regular_user_cannot_access_dashboard(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->get('/dashboard');

        $response
            ->assertRedirect(route('welcome'))
            ->assertSessionHas('error');
    }

    public function test_admin_user_can_access_dashboard(): void
    {
        Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $admin = $this->createVerifiedUser();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/dashboard')->assertOk();
    }

    private function createVerifiedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => 'password123',
            'email_verified_at' => now(),
        ], $attributes));
    }
}
