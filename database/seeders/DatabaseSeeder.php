<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the roles and permissions seeder first
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            AssignSuperAdminSeeder::class,
            StatusSeeder::class,
            LabelSeeder::class,
            CustomerLabelSeeder::class,
            UpdateExistingDataSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
