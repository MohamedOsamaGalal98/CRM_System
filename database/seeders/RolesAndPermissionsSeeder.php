<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions and roles
        \Spatie\Permission\Models\Permission::query()->delete();
        \Spatie\Permission\Models\Role::query()->delete();

        // Create permissions - ONLY the ones that exist in the code
        $permissions = [
            // Dashboard
            'view_dashboard',
            
            // User Management
            'view_any_users',
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'bulk_delete_users',
            'force_delete_users',
            'restore_users',
            'bulk_restore_users',
            'view_deleted_users',
            
            // Role Management
            'view_any_roles',
            'view_roles',
            'create_roles',
            'update_roles',
            'delete_roles',
            'bulk_delete_roles',
            'force_delete_roles',
            'restore_roles',
            'bulk_restore_roles',
            'view_deleted_roles',
            
            // Permission Management
            'view_any_permissions',
            'view_permissions',
            'create_permissions',
            'update_permissions',
            'delete_permissions',
            'bulk_delete_permissions',
            'force_delete_permissions',
            'restore_permissions',
            'bulk_restore_permissions',
            'view_deleted_permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $roles = [
            'Super Admin',
            'Admin',
            'Manager',
            'User'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Assign all permissions to Super Admin
        $superAdminRole = Role::findByName('Super Admin');
        $superAdminRole->givePermissionTo(Permission::all());

        // Assign permissions to Admin role (all except force delete)
        $adminRole = Role::findByName('Admin');
        $adminRole->givePermissionTo([
            // Dashboard
            'view_dashboard',
            
            // User Management (all except force delete)
            'view_any_users', 'view_users', 'create_users', 'update_users', 
            'delete_users', 'bulk_delete_users', 'restore_users', 
            'bulk_restore_users', 'view_deleted_users',
            
            // Role Management (all except force delete)
            'view_any_roles', 'view_roles', 'create_roles', 'update_roles',
            'delete_roles', 'bulk_delete_roles', 'restore_roles', 
            'bulk_restore_roles', 'view_deleted_roles',
            
            // Permission Management (view and basic operations only)
            'view_any_permissions', 'view_permissions', 'create_permissions', 
            'update_permissions', 'delete_permissions', 'bulk_delete_permissions',
            'restore_permissions', 'bulk_restore_permissions', 'view_deleted_permissions',
        ]);

        // Assign permissions to Manager (view + create + update only)
        $managerRole = Role::findByName('Manager');
        $managerRole->givePermissionTo([
            // Dashboard
            'view_dashboard',
            
            // User Management (basic operations)
            'view_any_users', 'view_users', 'create_users', 'update_users',
            
            // Role Management (view only)
            'view_any_roles', 'view_roles', 'create_roles', 'update_roles',
            
            // Permission Management (view only)
            'view_any_permissions', 'view_permissions', 'create_permissions', 'update_permissions',
        ]);

        // Assign permissions to User (view only)
        $userRole = Role::findByName('User');
        $userRole->givePermissionTo([
            // Dashboard
            'view_dashboard',
            
            // Basic view permissions
            'view_any_users', 'view_users',
        ]);
    }
}
