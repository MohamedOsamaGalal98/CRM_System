<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

echo "=== Dashboard Permission Test ===\n\n";

// Check if view_dashboard permission exists
$permission = Permission::where('name', 'view_dashboard')->first();
if ($permission) {
    echo "✅ 'view_dashboard' permission exists (ID: {$permission->id})\n";
} else {
    echo "❌ 'view_dashboard' permission does NOT exist\n";
    exit(1);
}

// Check roles that have this permission
$rolesWithPermission = Role::whereHas('permissions', function($query) {
    $query->where('name', 'view_dashboard');
})->get();

echo "\n📋 Roles with 'view_dashboard' permission:\n";
foreach ($rolesWithPermission as $role) {
    echo "  - {$role->name}\n";
}

// Check roles that DON'T have this permission
$rolesWithoutPermission = Role::whereDoesntHave('permissions', function($query) {
    $query->where('name', 'view_dashboard');
})->get();

echo "\n🚫 Roles WITHOUT 'view_dashboard' permission:\n";
if ($rolesWithoutPermission->count() > 0) {
    foreach ($rolesWithoutPermission as $role) {
        echo "  - {$role->name}\n";
    }
} else {
    echo "  (All roles have the permission)\n";
}

// Test with a sample user
$testUser = User::first();
if ($testUser) {
    echo "\n👤 Testing with user: {$testUser->name} ({$testUser->email})\n";
    if ($testUser->can('view_dashboard')) {
        echo "  ✅ User CAN access dashboard\n";
    } else {
        echo "  ❌ User CANNOT access dashboard\n";
    }
    
    echo "  📋 User roles: ";
    if ($testUser->roles->count() > 0) {
        echo $testUser->roles->pluck('name')->implode(', ') . "\n";
    } else {
        echo "(No roles assigned)\n";
    }
}

echo "\n=== Test Complete ===\n";
