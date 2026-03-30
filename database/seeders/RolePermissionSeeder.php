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

            // File Management
            'view files',
            'upload files',
            'delete files',
            'manage files',
            
            // Admin permissions
            'access admin panel',
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all()); // Admin has all permissions

        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $moderatorRole->givePermissionTo([
            'view users',
            'view content',
            'create content',
            'edit content',
            'delete content',
            'publish content',
            'view files',
            'upload files',
        ]);

        $userRole = Role::firstOrCreate(['name' => 'user']);
        $userRole->givePermissionTo([
            'view content',
            'create content',
            'edit content',
            'view files',
            'upload files',
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
}
