<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        try {
            $permissions = [
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
                'bulk_delete_statuses', 'force_delete_statuses', 'restore_statuses', 'bulk_restore_statuses', 'view_deleted_statuses',
            ];

            foreach ($permissions as $permission) {
                Gate::define($permission, function ($user) use ($permission) {
                    return $user->hasPermissionTo($permission);
                });
            }
        } catch (\Exception $e) {
        }

    }
}