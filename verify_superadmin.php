<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;

try {
    $user = User::where('email', 'superadmin@admin.com')->first();
    $allPermissions = Permission::all();
    
    if ($user) {
        echo "=== SUPER ADMIN PERMISSIONS VERIFICATION ===\n";
        echo "User: " . $user->name . " (" . $user->email . ")\n";
        echo "Total System Permissions: " . $allPermissions->count() . "\n";
        echo "User Permissions Count: " . $user->getAllPermissions()->count() . "\n";
        
        if ($user->getAllPermissions()->count() === $allPermissions->count()) {
            echo "✅ SUCCESS: User has ALL system permissions\n";
        } else {
            echo "❌ WARNING: User missing some permissions\n";
        }
        
        echo "\n=== PERMISSION BREAKDOWN ===\n";
        
        $permissionGroups = [
            'Users' => $allPermissions->filter(fn($p) => str_contains($p->name, 'user')),
            'Roles' => $allPermissions->filter(fn($p) => str_contains($p->name, 'role')),
            'Permissions' => $allPermissions->filter(fn($p) => str_contains($p->name, 'permission')),
            'Customers' => $allPermissions->filter(fn($p) => str_contains($p->name, 'customer')),
            'Leads' => $allPermissions->filter(fn($p) => str_contains($p->name, 'lead')),
            'Reports' => $allPermissions->filter(fn($p) => str_contains($p->name, 'report')),
            'System' => $allPermissions->filter(fn($p) => str_contains($p->name, 'system') || str_contains($p->name, 'backup') || str_contains($p->name, 'impersonate') || str_contains($p->name, 'analytics')),
        ];
        
        foreach ($permissionGroups as $group => $permissions) {
            if ($permissions->count() > 0) {
                echo "\n$group Permissions (" . $permissions->count() . "):\n";
                foreach ($permissions as $permission) {
                    $hasPermission = $user->can($permission->name);
                    $status = $hasPermission ? "✅" : "❌";
                    echo "  $status " . $permission->name . "\n";
                }
            }
        }
        
    } else {
        echo "❌ User superadmin@admin.com not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";
