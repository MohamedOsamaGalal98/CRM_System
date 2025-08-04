<?php

namespace App\Filament\Resources\PermissionResource\Widgets;

use App\Models\Permission;
use App\Models\Role;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PermissionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPermissions = Permission::count();
        $activePermissions = Permission::where('is_active', true)->count();
        $deletedPermissions = Permission::onlyTrashed()->count();
        
        // Permission usage statistics
        $assignedPermissions = DB::table('role_has_permissions')->distinct('permission_id')->count();
        $unusedPermissions = $totalPermissions - $assignedPermissions;
        $usageRate = $totalPermissions > 0 ? round(($assignedPermissions / $totalPermissions) * 100, 1) : 0;

        // Most used permission
        $popularPermission = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->select('permissions.name', DB::raw('count(*) as role_count'))
            ->groupBy('permissions.id', 'permissions.name')
            ->orderBy('role_count', 'desc')
            ->first();

        // Permission categories
        $permissions = Permission::all();
        $categories = [];
        foreach ($permissions as $permission) {
            $parts = explode('_', $permission->name);
            if (count($parts) >= 2) {
                $category = $parts[1];
                if (!isset($categories[$category])) {
                    $categories[$category] = 0;
                }
                $categories[$category]++;
            }
        }
        $categoryCount = count($categories);

        return [
            Stat::make('Total Permissions', $totalPermissions)
                ->description($activePermissions . ' active permissions')
                ->descriptionIcon('heroicon-m-key')
                ->color('primary'),

            Stat::make('Usage Rate', $usageRate . '%')
                ->description($assignedPermissions . ' assigned to roles')
                ->descriptionIcon('heroicon-m-link')
                ->color($usageRate >= 80 ? 'success' : ($usageRate >= 60 ? 'warning' : 'danger')),

            Stat::make('Most Used Permission', $popularPermission ? substr($popularPermission->name, 0, 20) . '...' : 'N/A')
                ->description($popularPermission ? 'Used by ' . $popularPermission->role_count . ' roles' : 'No usage')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Permission Categories', $categoryCount)
                ->description('Different resource types')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Unused Permissions', $unusedPermissions)
                ->description('Not assigned to any role')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($unusedPermissions > 0 ? 'warning' : 'success'),
        ];
    }
}
