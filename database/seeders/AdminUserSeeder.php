<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::updateOrCreate([
            'email' => 'admin@admin.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign admin role to the user
        $adminRole = Role::findByName('Admin');
        $admin->assignRole($adminRole);

        $superAdmin = User::updateOrCreate([
            'email' => 'superadmin@admin.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $superAdminRole = Role::findByName('Super Admin');
        $superAdmin->assignRole($superAdminRole);
    }
}
