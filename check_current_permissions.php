<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

try {
    $permissions = Permission::orderBy('name')->get();
    $roles = Role::with('permissions')->get();
    
    echo "=== CURRENT PERMISSIONS IN DATABASE ===\n";
    echo "Total: " . $permissions->count() . " permissions\n\n";
    
    $groups = [
        'Users' => $permissions->filter(fn($p) => str_contains($p->name, 'user')),
        'Roles' => $permissions->filter(fn($p) => str_contains($p->name, 'role')),
        'Permissions' => $permissions->filter(fn($p) => str_contains($p->name, 'permission')),
        'Other' => $permissions->filter(fn($p) => !str_contains($p->name, 'user') && !str_contains($p->name, 'role') && !str_contains($p->name, 'permission'))
    ];
    
    foreach ($groups as $group => $perms) {
        if ($perms->count() > 0) {
            echo "$group (" . $perms->count() . "):\n";
            foreach ($perms as $permission) {
                echo "  - " . $permission->name . "\n";
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
            foreach ($role->permissions as $permission) {
                echo "  - " . $permission->name . "\n";
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
