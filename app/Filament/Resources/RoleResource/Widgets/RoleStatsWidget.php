<?php

namespace App\Filament\Resources\RoleResource\Widgets;

use App\Models\Role;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class RoleStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRoles = Role::count();
        $activeRoles = Role::where('is_active', true)->count();
        $deletedRoles = Role::onlyTrashed()->count();
        
        // Users with roles
        $usersWithRoles = DB::table('model_has_roles')->distinct('model_id')->count();
        $totalUsers = User::count();
        $assignmentRate = $totalUsers > 0 ? round(($usersWithRoles / $totalUsers) * 100, 1) : 0;

        // Most assigned role
        $popularRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as user_count'))
            ->groupBy('roles.id', 'roles.name')
            ->orderBy('user_count', 'desc')
            ->first();

        // Permissions distribution
        $totalPermissions = DB::table('role_has_permissions')->count();
        $avgPermissionsPerRole = $totalRoles > 0 ? round($totalPermissions / $totalRoles, 1) : 0;

        return [
            Stat::make('Total Roles', $totalRoles)
                ->description($activeRoles . ' active roles')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('primary'),

            Stat::make('Role Assignment Rate', $assignmentRate . '%')
                ->description($usersWithRoles . ' of ' . $totalUsers . ' users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($assignmentRate >= 80 ? 'success' : ($assignmentRate >= 60 ? 'warning' : 'danger')),

            Stat::make('Most Popular Role', $popularRole ? $popularRole->name : 'N/A')
                ->description($popularRole ? $popularRole->user_count . ' users assigned' : 'No assignments')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Avg Permissions/Role', $avgPermissionsPerRole)
                ->description('Permission distribution')
                ->descriptionIcon('heroicon-m-key')
                ->color('info'),

            Stat::make('Deleted Roles', $deletedRoles)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}
