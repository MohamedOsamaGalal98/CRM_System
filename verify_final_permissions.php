<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

try {
    $permissions = Permission::orderBy('name')->get();
    $roles = Role::with('permissions')->get();
    $user = User::where('email', 'superadmin@admin.com')->first();
    
    echo "=== FINAL PERMISSIONS VERIFICATION ===\n";
    echo "Total Permissions: " . $permissions->count() . "\n\n";
    
    $groups = [
        'Dashboard' => $permissions->filter(fn($p) => str_contains($p->name, 'dashboard')),
        'Users' => $permissions->filter(fn($p) => str_contains($p->name, 'user')),
        'Roles' => $permissions->filter(fn($p) => str_contains($p->name, 'role')),
        'Permissions' => $permissions->filter(fn($p) => str_contains($p->name, 'permission')),
    ];
    
    foreach ($groups as $group => $perms) {
        if ($perms->count() > 0) {
            echo "$group (" . $perms->count() . "):\n";
            foreach ($perms as $permission) {
                echo "  ✅ " . $permission->name . "\n";
            }
            echo "\n";
        }
    }
    
    echo "=== ROLES AND THEIR PERMISSIONS ===\n";
    foreach ($roles as $role) {
        echo $role->name . " (" . $role->permissions->count() . " permissions):\n";
        if ($role->name === 'Super Admin') {
            echo "  [ALL PERMISSIONS]\n";
        } else {
            foreach ($role->permissions->sortBy('name') as $permission) {
                echo "  - " . $permission->name . "\n";
            }
        }
        echo "\n";
    }
    
    if ($user) {
        echo "=== SUPERADMIN USER STATUS ===\n";
        echo "User: " . $user->name . " (" . $user->email . ")\n";
        echo "Total User Permissions: " . $user->getAllPermissions()->count() . "\n";
        echo "Has Super Admin Role: " . ($user->hasRole('Super Admin') ? '✅ YES' : '❌ NO') . "\n";
        echo "Can Access Dashboard: " . ($user->can('view_dashboard') ? '✅ YES' : '❌ NO') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
