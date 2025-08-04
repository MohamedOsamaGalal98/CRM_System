<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

echo "=== Fixing User Roles and Testing Dashboard Permission ===\n\n";

// Find admin user
$adminUser = User::where('email', 'admin@admin.com')->first();
if ($adminUser) {
    echo "👤 Found admin user: {$adminUser->name}\n";
    
    // Assign Admin role to admin user
    $adminRole = Role::where('name', 'Admin')->first();
    if ($adminRole && !$adminUser->hasRole('Admin')) {
        $adminUser->assignRole('Admin');
        echo "✅ Assigned 'Admin' role to admin user\n";
    }
    
    // Test permission after role assignment
    echo "🔍 Testing dashboard access after role assignment:\n";
    if ($adminUser->can('view_dashboard')) {
        echo "  ✅ Admin user CAN access dashboard\n";
    } else {
        echo "  ❌ Admin user CANNOT access dashboard\n";
    }
}

// Create a test role without dashboard permission
$testRole = Role::where('name', 'No Dashboard Access')->first();
if (!$testRole) {
    echo "\n🆕 Creating 'No Dashboard Access' role...\n";
    $testRole = Role::create([
        'name' => 'No Dashboard Access',
        'guard_name' => 'web',
        'is_active' => true
    ]);
    
    // Give it some basic permissions but NOT view_dashboard
    $basicPermissions = [
        'view_users',
        'view_roles'
    ];
    
    foreach ($basicPermissions as $permName) {
        $perm = Permission::where('name', $permName)->first();
        if ($perm) {
            $testRole->givePermissionTo($perm);
            echo "  ✅ Added '{$permName}' permission to test role\n";
        }
    }
    echo "✅ Created test role without dashboard access\n";
}

// Create a test user with the no-dashboard role
$testUser = User::where('email', 'test@test.com')->first();
if (!$testUser) {
    echo "\n🆕 Creating test user without dashboard access...\n";
    $testUser = User::create([
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => bcrypt('password'),
        'email_verified_at' => now(),
        'is_active' => true
    ]);
    $testUser->assignRole('No Dashboard Access');
    echo "✅ Created test user and assigned 'No Dashboard Access' role\n";
}

// Test permissions for both users
echo "\n=== Permission Test Results ===\n";

if ($adminUser) {
    echo "👤 Admin User ({$adminUser->email}):\n";
    echo "  Dashboard Access: " . ($adminUser->can('view_dashboard') ? "✅ YES" : "❌ NO") . "\n";
    echo "  Roles: " . ($adminUser->roles->count() > 0 ? $adminUser->roles->pluck('name')->implode(', ') : 'None') . "\n";
}

if ($testUser) {
    echo "\n👤 Test User ({$testUser->email}):\n";
    echo "  Dashboard Access: " . ($testUser->can('view_dashboard') ? "✅ YES" : "❌ NO") . "\n";
    echo "  Roles: " . ($testUser->roles->count() > 0 ? $testUser->roles->pluck('name')->implode(', ') : 'None') . "\n";
}

echo "\n🔧 Now you can test by logging in as:\n";
echo "  - admin@admin.com (should see Dashboard)\n";
echo "  - test@test.com (should NOT see Dashboard)\n";
echo "  - Password for both: password\n";

echo "\n=== Setup Complete ===\n";
