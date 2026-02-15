<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_login_redirects_to_profile(): void
    {
        Role::create(['name' => 'user']);

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        $response = $this->post(route('login.store'), [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'email' => 'admin-login@example.com',
            'password' => 'password123',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $response = $this->post(route('login.store'), [
            'email' => 'admin-login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_regular_user_cannot_access_dashboard_route(): void
    {
        Role::create(['name' => 'user']);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->get(route('dashboard'));

        $response->assertRedirect(route('profile.show'));
        $response->assertSessionHas('error');
    }

    public function test_authenticated_regular_user_visiting_login_is_redirected_to_profile(): void
    {
        Role::create(['name' => 'user']);

        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        $response = $this
            ->actingAs($user)
            ->get(route('login'));

        $response->assertRedirect('/profile');
    }
}
