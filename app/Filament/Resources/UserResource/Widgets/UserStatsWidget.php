<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class UserStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        
        // This month's statistics
        $thisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $lastMonth = User::where('created_at', '>=', Carbon::now()->subMonth()->startOfMonth())
            ->where('created_at', '<', Carbon::now()->startOfMonth())
            ->count();
        $monthlyGrowth = $lastMonth > 0 ? (($thisMonth - $lastMonth) / $lastMonth) * 100 : 100;

        // This week's statistics
        $thisWeek = User::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $deletedUsers = User::onlyTrashed()->count();

        return [
            Stat::make('Total Users', $totalUsers)
                ->description($thisMonth . ' new this month')
                ->descriptionIcon($monthlyGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyGrowth >= 0 ? 'success' : 'danger'),

            Stat::make('Active Users', $activeUsers)
                ->description(round(($activeUsers / max($totalUsers, 1)) * 100, 1) . '% of total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Verified Users', $verifiedUsers)
                ->description(round(($verifiedUsers / max($totalUsers, 1)) * 100, 1) . '% verified')
                ->descriptionIcon('heroicon-m-envelope-open')
                ->color('info'),

            Stat::make('New This Week', $thisWeek)
                ->description('Recent registrations')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Deleted Users', $deletedUsers)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}
