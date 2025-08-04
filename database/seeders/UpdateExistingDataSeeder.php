<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UpdateExistingDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تحديث كل المستخدمين الموجودين ليكونوا نشطين
        User::whereNull('is_active')->update(['is_active' => true]);
        
        // تحديث كل الأدوار الموجودة لتكون نشطة
        Role::whereNull('is_active')->update(['is_active' => true]);
        
        // تحديث كل الصلاحيات الموجودة لتكون نشطة
        Permission::whereNull('is_active')->update(['is_active' => true]);
        
        $this->command->info('✅ Updated existing users, roles, and permissions to be active');
    }
}
