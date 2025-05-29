<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Define permissions (removed unwanted permissions)
        $permissions = [
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'assign-role',
            // 'view-content', // Removed
            // 'view-reports', // Removed
            // 'create-users'  // Removed
        ];

        // Create permissions with 'api' guard
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }

        // Create roles with 'api' guard
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);
        
        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'api'
        ]);

        $accountantRole = Role::firstOrCreate([
            'name' => 'accountant',
            'guard_name' => 'api'
        ]);

        $viewerRole = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'api'
        ]);

        // Assign permissions to roles
        $adminRole->syncPermissions($permissions); // Admin gets all permissions

        // User only gets 'role-list'
        $userRole->syncPermissions(['role-list']);

        // Accountant gets 'role-list' only (removed view-content, view-reports, create-users)
        $accountantRole->syncPermissions([
            'role-list',
        ]);

        // Viewer only gets 'role-list' (removed view-content)
        $viewerRole->syncPermissions([
            'role-list',
        ]);
    }
}
