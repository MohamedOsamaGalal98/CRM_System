<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SystemOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // Users statistics
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $deletedUsers = User::onlyTrashed()->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        
        // Recent users (last 30 days)
        $recentUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $previousMonthUsers = User::where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))->count();
        $userGrowth = $previousMonthUsers > 0 
            ? (($recentUsers - $previousMonthUsers) / $previousMonthUsers) * 100 
            : 100;

        // Roles statistics
        $totalRoles = Role::count();
        $activeRoles = Role::where('is_active', true)->count();
        
        // Permissions statistics
        $totalPermissions = Permission::count();
        $activePermissions = Permission::where('is_active', true)->count();

        // User role distribution
        $usersWithRoles = DB::table('model_has_roles')
            ->distinct('model_id')
            ->count();
        $usersWithoutRoles = $totalUsers - $usersWithRoles;

        return [
            Stat::make('Total Users', $totalUsers)
                ->description($recentUsers . ' new users this month')
                ->descriptionIcon($userGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userGrowth >= 0 ? 'success' : 'danger')
                ->chart($this->getUsersChart()),

            Stat::make('Active Users', $activeUsers)
                ->description(round(($activeUsers / max($totalUsers, 1)) * 100, 1) . '% of total users')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Email Verified', $verifiedUsers)
                ->description(round(($verifiedUsers / max($totalUsers, 1)) * 100, 1) . '% verified')
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color('info'),

            Stat::make('Total Roles', $totalRoles)
                ->description($activeRoles . ' active roles')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning'),

            Stat::make('Total Permissions', $totalPermissions)
                ->description($activePermissions . ' active permissions')
                ->descriptionIcon('heroicon-m-key')
                ->color('primary'),

            Stat::make('Users with Roles', $usersWithRoles)
                ->description($usersWithoutRoles . ' without roles')
                ->descriptionIcon('heroicon-m-user-group')
                ->color($usersWithoutRoles > 0 ? 'warning' : 'success'),
        ];
    }

    private function getUsersChart(): array
    {
        // Get user registrations for the last 7 days
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = User::whereDate('created_at', $date)->count();
            $data[] = $count;
        }
        return $data;
    }

    protected static ?int $sort = 1;
}
