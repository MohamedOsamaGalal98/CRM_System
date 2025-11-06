<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions and roles completely
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        \Spatie\Permission\Models\Permission::query()->forceDelete();
        \Spatie\Permission\Models\Role::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

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
            
            // Customer Management
            'view_any_customers',
            'view_customers',
            'create_customers',
            'update_customers',
            'delete_customers',
            'bulk_delete_customers',
            'force_delete_customers',
            'restore_customers',
            'bulk_restore_customers',
            'view_deleted_customers',
            
            // Label Management
            'view_any_labels',
            'view_labels',
            'create_labels',
            'update_labels',
            'delete_labels',
            'bulk_delete_labels',
            'force_delete_labels',
            'restore_labels',
            'bulk_restore_labels',
            'view_deleted_labels',
            
            // Status Management
            'view_any_statuses',
            'view_statuses',
            'create_statuses',
            'update_statuses',
            'delete_statuses',
            'bulk_delete_statuses',
            'force_delete_statuses',
            'restore_statuses',
            'bulk_restore_statuses',
            'view_deleted_statuses',
      
            // Lead Source Management
            'view_any_lead_sources',
            'view_lead_sources',
            'create_lead_sources',
            'update_lead_sources',
            'delete_lead_sources',
            'bulk_delete_lead_sources',
            'force_delete_lead_sources',
            'restore_lead_sources',
            'bulk_restore_lead_sources',
            'view_deleted_lead_sources',
            
            // Custom Field Management
            'view_any_custom_fields',
            'view_custom_fields',
            'create_custom_fields',
            'update_custom_fields',
            'delete_custom_fields',
            'bulk_delete_custom_fields',
            'force_delete_custom_fields',
            'restore_custom_fields',
            'bulk_restore_custom_fields',
            'view_deleted_custom_fields',
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

        // Assign specific permissions to Super Admin (not all permissions)
        $superAdminRole = Role::findByName('Super Admin');
        $superAdminRole->givePermissionTo([
            // User Management
            'view_any_users', 'view_users', 'create_users', 'update_users', 'delete_users',
            'bulk_delete_users', 'force_delete_users', 'restore_users', 'bulk_restore_users', 'view_deleted_users',
            
            // Role Management  
            'view_any_roles', 'view_roles', 'create_roles', 'update_roles', 'delete_roles',
            'bulk_delete_roles', 'force_delete_roles', 'restore_roles', 'bulk_restore_roles', 'view_deleted_roles',
            
            // Permission Management
            'view_any_permissions', 'view_permissions', 'create_permissions', 'update_permissions', 'delete_permissions',
            'bulk_delete_permissions', 'force_delete_permissions', 'restore_permissions', 'bulk_restore_permissions', 'view_deleted_permissions',
            
            // Customer Management
            'view_any_customers', 'view_customers', 'create_customers', 'update_customers', 'delete_customers',
            'bulk_delete_customers', 'force_delete_customers', 'restore_customers', 'bulk_restore_customers', 'view_deleted_customers',
            
            // Label Management
            'view_any_labels', 'view_labels', 'create_labels', 'update_labels', 'delete_labels',
            'bulk_delete_labels', 'force_delete_labels', 'restore_labels', 'bulk_restore_labels', 'view_deleted_labels',
            
            // Status Management 
            'view_any_statuses', 'view_statuses', 'create_statuses', 'update_statuses', 'delete_statuses',
            'bulk_delete_statuses',
            'force_delete_statuses', 'restore_statuses', 'bulk_restore_statuses', 'view_deleted_statuses', 

            // Lead Source Management
            'view_any_lead_sources', 'view_lead_sources', 'create_lead_sources', 'update_lead_sources', 'delete_lead_sources',
            'bulk_delete_lead_sources',
            'force_delete_lead_sources', 'restore_lead_sources', 'bulk_restore_lead_sources', 'view_deleted_lead_sources', 
        ]);

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

        // Assign Super Admin role to user named "super admin"
        $superAdminUser = User::where('name', 'super admin')->first();
        if ($superAdminUser) {
            $superAdminUser->assignRole('Super Admin');
        }
    }
}
