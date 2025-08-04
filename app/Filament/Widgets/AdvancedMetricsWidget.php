<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected function getStats(): array
    {
        // Calculate advanced metrics
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisWeek = Carbon::now()->startOfWeek();
        $lastWeek = Carbon::now()->subWeek()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        // Daily metrics
        $todayUsers = User::whereDate('created_at', $today)->count();
        $yesterdayUsers = User::whereDate('created_at', $yesterday)->count();
        $dailyChange = $yesterdayUsers > 0 ? (($todayUsers - $yesterdayUsers) / $yesterdayUsers) * 100 : 100;

        // Weekly metrics
        $thisWeekUsers = User::where('created_at', '>=', $thisWeek)->count();
        $lastWeekUsers = User::where('created_at', '>=', $lastWeek)->where('created_at', '<', $thisWeek)->count();
        $weeklyChange = $lastWeekUsers > 0 ? (($thisWeekUsers - $lastWeekUsers) / $lastWeekUsers) * 100 : 100;

        // Monthly metrics
        $thisMonthUsers = User::where('created_at', '>=', $thisMonth)->count();
        $lastMonthUsers = User::where('created_at', '>=', $lastMonth)->where('created_at', '<', $thisMonth)->count();
        $monthlyChange = $lastMonthUsers > 0 ? (($thisMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100 : 100;

        // Average users per role
        $totalUsers = User::count();
        $totalRoles = Role::count();
        $avgUsersPerRole = $totalRoles > 0 ? round($totalUsers / $totalRoles, 1) : 0;

        // Most popular role
        $popularRole = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as user_count'))
            ->groupBy('roles.id', 'roles.name')
            ->orderBy('user_count', 'desc')
            ->first();

        // Permissions per role average
        $totalPermissions = DB::table('role_has_permissions')->count();
        $avgPermissionsPerRole = $totalRoles > 0 ? round($totalPermissions / $totalRoles, 1) : 0;

        // User engagement metrics
        $verificationRate = $totalUsers > 0 ? round((User::whereNotNull('email_verified_at')->count() / $totalUsers) * 100, 1) : 0;
        $activeRate = $totalUsers > 0 ? round((User::where('is_active', true)->count() / $totalUsers) * 100, 1) : 0;

        return [
            Stat::make('Today New Users', $todayUsers)
                ->description(abs(round($dailyChange, 1)) . '% vs yesterday')
                ->descriptionIcon($dailyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($dailyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Weekly Growth', $thisWeekUsers)
                ->description(abs(round($weeklyChange, 1)) . '% vs last week')
                ->descriptionIcon($weeklyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($weeklyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Monthly Growth', $thisMonthUsers)
                ->description(abs(round($monthlyChange, 1)) . '% vs last month')
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger'),

            Stat::make('Avg Users/Role', $avgUsersPerRole)
                ->description('Distribution balance')
                ->descriptionIcon('heroicon-m-scale')
                ->color('info'),

            Stat::make('Most Popular Role', $popularRole ? $popularRole->name : 'N/A')
                ->description($popularRole ? $popularRole->user_count . ' users' : 'No data')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('System Health', $activeRate . '%')
                ->description('Active user rate')
                ->descriptionIcon('heroicon-m-heart')
                ->color($activeRate >= 80 ? 'success' : ($activeRate >= 60 ? 'warning' : 'danger')),
        ];
    }
}
