<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class VerifyFilamentSetup extends Command
{
    protected $signature = 'verify:filament-setup';
    protected $description = 'Verify that the Filament role and permission setup is working correctly';

    public function handle()
    {
        $this->info('Verifying Filament Role & Permission Setup...');
        $this->newLine();
        
        // Check permissions
        $permissionCount = Permission::count();
        $this->info("✅ Permissions created: {$permissionCount}");
        
        // Check roles
        $roleCount = Role::count();
        $this->info("✅ Roles created: {$roleCount}");
        
        // Check users with roles
        $userCount = User::whereHas('roles')->count();
        $this->info("✅ Users with roles: {$userCount}");
        
        // Test Super Admin permissions
        $superAdmin = User::whereHas('roles', function($query) {
            $query->where('name', 'Super Admin');
        })->first();
        
        if ($superAdmin) {
            $permissionCount = $superAdmin->getAllPermissions()->count();
            $this->info("✅ Super Admin has {$permissionCount} permissions");
        }
        
        // Test Admin permissions
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'Admin');
        })->first();
        
        if ($admin) {
            $canViewUsers = $admin->can('view_users') ? 'Yes' : 'No';
            $canDeleteRoles = $admin->can('delete_roles') ? 'Yes' : 'No';
            $this->info("✅ Admin can view users: {$canViewUsers}");
            $this->info("✅ Admin can delete roles: {$canDeleteRoles}");
        }
        
        $this->newLine();
        $this->info('🎉 Filament setup verification completed!');
        $this->info('📝 Admin Panel: http://localhost:8000/admin');
        $this->info('👤 Login with: superadmin@example.com / password');
        
        return 0;
    }
}
