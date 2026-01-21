<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder demonstrates:
     * - Creating users with relationships
     * - Assigning roles and permissions
     * - Using factories for bulk data
     * - Proper data organization
     */
    public function run(): void
    {
        // Create admin user (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Create moderator user (idempotent)
        $moderator = User::firstOrCreate(
            ['email' => 'moderator@example.com'],
            [
                'name' => 'Moderator User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $moderator->hasRole('moderator')) {
            $moderator->assignRole('moderator');
        }

        // Create regular users
        $users = User::factory()->count(10)->create()->each(function ($user) {
            $user->assignRole('user');
        });

        // Create a fixed test user (idempotent)
        $testUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (! $testUser->hasRole('user')) {
            $testUser->assignRole('user');
        }

        // Demo posts (only if posts table exists)
        try {
            // Admin gets some published posts
            Post::factory()->count(3)->published()->create(['user_id' => $admin->id]);

            // Random users get draft/published posts
            $allUsers = $users->pluck('id')->push($moderator->id)->push($testUser->id)->unique()->values();
            foreach ($allUsers as $uid) {
                Post::factory()->count(2)->create(['user_id' => $uid]);
            }
        } catch (\Throwable $e) {
            // In case the example migration wasn't applied, we keep seeding users/roles without failing.
        }

        $this->command->info('Demo users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Moderator: moderator@example.com / password');
        $this->command->info('User: user@example.com / password');
    }
}
