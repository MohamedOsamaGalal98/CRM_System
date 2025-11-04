<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or check Super Admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdmin = User::whereHas('roles', function($q) {
            $q->where('name', 'Super Admin');
        })->first();

        if (!$superAdmin) {
            $this->info('لا يوجد مستخدم Super Admin! سأنشئ واحد...');
            
            $superAdmin = User::firstOrCreate([
                'email' => 'superadmin@admin.com'
            ], [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            
            $role = Role::findByName('Super Admin');
            $superAdmin->assignRole($role);
            
            $this->info('تم إنشاء Super Admin بنجاح!');
            $this->info('Email: superadmin@admin.com');
            $this->info('Password: password');
        } else {
            $this->info("Super Admin موجود: {$superAdmin->name} ({$superAdmin->email})");
            
            if (!$superAdmin->hasRole('Super Admin')) {
                $role = Role::findByName('Super Admin');
                $superAdmin->assignRole($role);
                $this->info('تم تعيين دور Super Admin للمستخدم');
            }
            
            if (!$superAdmin->is_active) {
                $superAdmin->is_active = true;
                $superAdmin->save();
                $this->info('تم تفعيل المستخدم');
            }
        }

        $this->info("\nالأدوار المتاحة للـ Super Admin:");
        foreach ($superAdmin->getRoleNames() as $role) {
            $this->info("- {$role}");
        }

        $this->info("\nعدد الأذونات: " . $superAdmin->getAllPermissions()->count());
        
        return 0;
    }
}
