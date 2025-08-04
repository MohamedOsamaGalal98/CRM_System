<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    public function run()
    {
        // حذف جميع الصلاحيات والأدوار الموجودة
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('model_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('role_has_permissions')->truncate();
        Permission::query()->forceDelete();
        Role::query()->forceDelete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        // إنشاء صلاحيات شاملة للمستخدمين
        $userPermissions = [
            'view_users',
            'view_any_users', 
            'create_users',
            'update_users',
            'delete_users',
            'restore_users',
            'force_delete_users',
            'view_deleted_users',
            'bulk_delete_users',
            'bulk_restore_users',
            'assign_roles_users',
        ];

        // إنشاء صلاحيات شاملة للأدوار
        $rolePermissions = [
            'view_roles',
            'view_any_roles',
            'create_roles', 
            'update_roles',
            'delete_roles',
            'restore_roles',
            'force_delete_roles',
            'view_deleted_roles',
            'bulk_delete_roles',
            'bulk_restore_roles',
            'assign_permissions_roles',
        ];

        // إنشاء صلاحيات شاملة للصلاحيات
        $permissionPermissions = [
            'view_permissions',
            'view_any_permissions',
            'create_permissions',
            'update_permissions', 
            'delete_permissions',
            'restore_permissions',
            'force_delete_permissions',
            'view_deleted_permissions',
            'bulk_delete_permissions',
            'bulk_restore_permissions',
            'assign_roles_permissions',
        ];   

        // دمج جميع الصلاحيات
        $allPermissions = array_merge(
            $userPermissions,
            $rolePermissions, 
            $permissionPermissions,
        );

        // إنشاء الصلاحيات في قاعدة البيانات
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ], [
                'is_active' => true,
            ]);
        }

        // إنشاء أدوار أساسية
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ], [
            'is_active' => true,
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'web',
        ], [
            'is_active' => true,
        ]);

        $manager = Role::firstOrCreate([
            'name' => 'Manager',
            'guard_name' => 'web',
        ], [
            'is_active' => true,
        ]);

        $user = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web',
        ], [
            'is_active' => true,
        ]);

        // إعطاء جميع الصلاحيات للـ Super Admin
        $superAdmin->givePermissionTo($allPermissions);

        // إعطاء صلاحيات محدودة للـ Admin
        $adminPermissions = array_merge(
            $userPermissions,
            $rolePermissions,
            [
                'view_dashboard',
                'view_analytics', 
                'export_reports',
                'view_logs',
            ]
        );
        $admin->givePermissionTo($adminPermissions);

        // إعطاء صلاحيات أقل للـ Manager
        $managerPermissions = [
            'view_users',
            'view_any_users',
            'create_users', 
            'update_users',
            'view_roles',
            'view_any_roles',
            'view_dashboard',
            'export_users',
        ];
        $manager->givePermissionTo($managerPermissions);

        // إعطاء صلاحيات أساسية للـ User
        $userBasicPermissions = [
            'view_dashboard',
        ];
        $user->givePermissionTo($userBasicPermissions);

        // إعطاء دور Super Admin للمستخدم الأول
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->assignRole('Super Admin');
        }

        $this->command->info('✅ Permissions and Roles seeded successfully!');
        $this->command->info('📊 Total Permissions: ' . count($allPermissions));
        $this->command->info('👥 Total Roles: 4');
    }
}
