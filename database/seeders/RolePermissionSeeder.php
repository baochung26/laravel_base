<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage users', // Combined permission for all user management
            
            // Role & Permission Management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            'assign roles',
            'manage roles', // Combined permission for all role management
            
            // Content Management (example)
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            
            // Admin permissions
            'access admin panel',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Admin has all permissions

        $moderatorRole = Role::create(['name' => 'moderator']);
        $moderatorRole->givePermissionTo([
            'view users',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
        ]);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view content',
            'create content',
            'edit content',
        ]);

        // Create a demo admin user (optional)
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create a demo regular user (optional)
        $user = \App\Models\User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);
        $user->assignRole($userRole);

        $this->command->info('Roles and permissions created successfully!');
        $this->command->info('Admin credentials: admin@example.com / password');
        $this->command->info('User credentials: user@example.com / password');
    }
}
