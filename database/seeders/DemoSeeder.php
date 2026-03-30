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
        $defaultPassword = 'password';
        $demoUsers = collect([
            ['name' => 'Admin Chung DZ', 'email' => 'admin@example.com', 'role' => 'admin', 'active' => true],
            ['name' => 'Bao Vy', 'email' => 'baovy@example.com', 'role' => 'admin', 'active' => true],
            ['name' => 'Moderator User', 'email' => 'moderator@example.com', 'role' => 'moderator', 'active' => true],
            ['name' => 'Chung Phung', 'email' => 'phungbaochung@gmail.com', 'role' => 'user', 'active' => true],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'John Doe', 'email' => 'user@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Inactive User', 'email' => 'inactive@example.com', 'role' => 'user', 'active' => false],
            ['name' => 'Long Nguyen', 'email' => 'long.nguyen@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Minh Tran', 'email' => 'minh.tran@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Linh Pham', 'email' => 'linh.pham@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Khanh Le', 'email' => 'khanh.le@example.com', 'role' => 'user', 'active' => false],
            ['name' => 'Thao Vo', 'email' => 'thao.vo@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Huy Hoang', 'email' => 'huy.hoang@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Tien Bui', 'email' => 'tien.bui@example.com', 'role' => 'user', 'active' => true],
            ['name' => 'Quynh Anh', 'email' => 'quynh.anh@example.com', 'role' => 'user', 'active' => false],
        ]);

        $createdUsers = collect();
        foreach ($demoUsers as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => Hash::make($defaultPassword),
                    'email_verified_at' => $item['active'] ? now() : null,
                ]
            );

            // Keep demo data in sync if account already existed.
            $user->name = $item['name'];
            $user->password = $defaultPassword;
            $user->email_verified_at = $item['active'] ? ($user->email_verified_at ?? now()) : null;
            $user->save();

            if (! $user->hasRole($item['role'])) {
                $user->syncRoles([$item['role']]);
            }

            $createdUsers->push($user);
        }

        // Create additional deterministic users for pagination demo (idempotent)
        foreach (range(1, 35) as $index) {
            $email = sprintf('demo.user.%03d@example.com', $index);
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => sprintf('Demo User %03d', $index),
                    'password' => Hash::make($defaultPassword),
                    'email_verified_at' => $index % 5 === 0 ? null : now()->subDays($index),
                ]
            );

            if (! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            $createdUsers->push($user);
        }

        // Demo posts (only if posts table exists)
        try {
            $admin = $createdUsers->firstWhere('email', 'admin@example.com');
            // Admin gets some published posts
            if ($admin) {
                Post::factory()->count(3)->published()->create(['user_id' => $admin->id]);
            }

            // Random users get draft/published posts
            $allUsers = $createdUsers->pluck('id')->unique()->values();
            foreach ($allUsers as $uid) {
                Post::factory()->count(2)->create(['user_id' => $uid]);
            }
        } catch (\Throwable $e) {
            // In case the example migration wasn't applied, we keep seeding users/roles without failing.
        }

        $activeCount = User::query()->whereNotNull('email_verified_at')->count();
        $inactiveCount = User::query()->whereNull('email_verified_at')->count();
        $this->command->info('Demo users seeded successfully.');
        $this->command->info('Credentials: <email> / password');
        $this->command->info('Total users: ' . User::query()->count());
        $this->command->info('Active users: ' . $activeCount);
        $this->command->info('Inactive users: ' . $inactiveCount);
    }
}
