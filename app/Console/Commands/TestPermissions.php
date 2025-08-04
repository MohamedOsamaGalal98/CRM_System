<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class TestPermissions extends Command
{
    protected $signature = 'test:permissions';
    protected $description = 'Test if the permissions system is working correctly';

    public function handle()
    {
        $this->info('Testing Spatie Laravel Permission Setup...');
        
        // Check if roles exist
        $roles = Role::all();
        $this->info('Roles found: ' . $roles->count());
        
        foreach ($roles as $role) {
            $this->info('- ' . $role->name);
        }
        
        // Check if admin users exist and have roles
        $adminUsers = User::whereHas('roles', function($query) {
            $query->whereIn('name', ['Admin', 'Super Admin']);
        })->get();
        
        $this->info('Admin users found: ' . $adminUsers->count());
        
        foreach ($adminUsers as $user) {
            $this->info('- ' . $user->name . ' (' . $user->email . ') - Roles: ' . $user->roles->pluck('name')->implode(', '));
        }
        
        // Test role checking
        $testUser = User::where('email', 'admin@example.com')->first();
        if ($testUser) {
            $this->info('Testing role check for admin@example.com:');
            $this->info('- Has Admin role: ' . ($testUser->hasRole('Admin') ? 'Yes' : 'No'));
            $this->info('- Has Super Admin role: ' . ($testUser->hasRole('Super Admin') ? 'Yes' : 'No'));
        }
        
        $this->info('Permission system test completed!');
        
        return 0;
    }
}
