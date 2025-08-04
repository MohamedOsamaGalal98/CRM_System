<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Find the user by email
        $superAdminUser = User::where('email', 'superadmin@admin.com')->first();
        
        if (!$superAdminUser) {
            $this->command->error('User superadmin@admin.com not found!');
            return;
        }

        // Make sure Super Admin role exists
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);

        // Get all permissions and assign them to Super Admin role
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        
        // Assign Super Admin role to the user
        $superAdminUser->assignRole('Super Admin');
        
        // Also give all permissions directly to the user (belt and suspenders approach)
        $superAdminUser->syncPermissions($allPermissions);

        $this->command->info("Successfully assigned Super Admin role with all permissions to {$superAdminUser->email}");
        $this->command->info("Total permissions assigned: " . $allPermissions->count());
        $this->command->info("Permissions: " . $allPermissions->pluck('name')->implode(', '));
    }
}
