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
            'Sales Manager',
            'Sales',
            'Dataentry Manager',
            'Dataentry',
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

        // Assign permissions to Sales Manager
        $salesManagerRole = Role::findByName('Sales Manager');
        $salesManagerRole->givePermissionTo([
            
            // User Management (basic operations)
            'view_any_users', 'view_users', 'create_users', 'update_users',
            
            // Role Management (view only)
            'view_any_roles', 'view_roles',
            
            // Permission Management (view only)
            'view_any_permissions', 'view_permissions',
        ]);

        // Assign permissions to Sales (view only)
        $salesRole = Role::findByName('Sales');
        $salesRole->givePermissionTo([
            
            // Basic view permissions
            'view_any_users', 'view_users',
        ]);

        // Assign permissions to Dataentry Manager
        $dataentryManagerRole = Role::findByName('Dataentry Manager');
        $dataentryManagerRole->givePermissionTo([
            // User Management (basic operations)
            'view_any_users', 'view_users', 'create_users', 'update_users',
            
            // Role Management (view only)
            'view_any_roles', 'view_roles',
            
            // Permission Management (view only)
            'view_any_permissions', 'view_permissions',
        ]);

        // Assign permissions to Dataentry (view only)
        $dataentryRole = Role::findByName('Dataentry');
        $dataentryRole->givePermissionTo([
            
            // Basic view permissions
            'view_any_users', 'view_users',
        ]);
    }
}
