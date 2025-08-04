<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

// Check if view_dashboard permission exists
$permission = Permission::where('name', 'view_dashboard')->first();

if (!$permission) {
    echo "Creating view_dashboard permission...\n";
    $permission = Permission::create([
        'name' => 'view_dashboard',
        'guard_name' => 'web',
        'is_active' => true
    ]);
    echo "Permission created with ID: " . $permission->id . "\n";
} else {
    echo "Permission 'view_dashboard' already exists with ID: " . $permission->id . "\n";
}

// Check if super-admin role has this permission
$superAdminRole = Role::where('name', 'super-admin')->first();
if ($superAdminRole) {
    if (!$superAdminRole->hasPermissionTo('view_dashboard')) {
        echo "Assigning view_dashboard permission to super-admin role...\n";
        $superAdminRole->givePermissionTo('view_dashboard');
        echo "Permission assigned to super-admin role.\n";
    } else {
        echo "Super-admin role already has view_dashboard permission.\n";
    }
}

// Check admin role if exists
$adminRole = Role::where('name', 'admin')->first();
if ($adminRole) {
    if (!$adminRole->hasPermissionTo('view_dashboard')) {
        echo "Assigning view_dashboard permission to admin role...\n";
        $adminRole->givePermissionTo('view_dashboard');
        echo "Permission assigned to admin role.\n";
    } else {
        echo "Admin role already has view_dashboard permission.\n";
    }
}

echo "Dashboard permission setup completed!\n";
