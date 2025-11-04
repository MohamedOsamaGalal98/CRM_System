<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CheckUser extends Command
{
    protected $signature = 'check:user {email}';
    protected $description = 'Check user permissions';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found');
            return;
        }
        
        $this->info('User: ' . $user->name);
        $this->info('Active: ' . ($user->is_active ? 'Yes' : 'No'));
        $this->info('Roles: ' . $user->roles->pluck('name')->implode(', '));
        
        $superadminRole = Role::where('name', 'Super Admin')->first();
        if ($superadminRole && !$user->hasRole('Super Admin')) {
            $user->assignRole('Super Admin');
            $this->info('Assigned Super Admin role');
        }
        
        $user->is_active = true;
        $user->save();
        $this->info('User activated');
    }
}
