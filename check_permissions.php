<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

try {
    $user = User::where('email', 'superadmin@admin.com')->first();
    
    if ($user) {
        echo "User found: " . $user->name . "\n";
        echo "Email: " . $user->email . "\n";
        echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
        echo "Direct Permissions Count: " . $user->permissions->count() . "\n";
        echo "All Permissions Count (via roles): " . $user->getAllPermissions()->count() . "\n";
        
        if ($user->hasRole('Super Admin')) {
            echo "✅ User has Super Admin role\n";
        } else {
            echo "❌ User does not have Super Admin role\n";
        }
        
        // Test some specific permissions
        $testPermissions = ['view_users', 'create_users', 'delete_users', 'view_roles', 'create_roles'];
        foreach ($testPermissions as $permission) {
            if ($user->can($permission)) {
                echo "✅ Has permission: $permission\n";
            } else {
                echo "❌ Missing permission: $permission\n";
            }
        }
        
    } else {
        echo "❌ User superadmin@admin.com not found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
