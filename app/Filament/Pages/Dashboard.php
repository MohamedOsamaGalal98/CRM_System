<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Gate;
use App\Filament\Widgets\SystemOverviewWidget;
use App\Filament\Widgets\UsersAnalyticsChart;
use App\Filament\Widgets\RoleDistributionChart;
use App\Filament\Widgets\UserStatusChart;
use App\Filament\Widgets\EmailVerificationChart;
use App\Filament\Widgets\LatestUsersWidget;
use App\Filament\Widgets\PermissionsDistributionChart;
use App\Filament\Widgets\AdvancedMetricsWidget;
use App\Filament\Widgets\UserRegistrationTrendsChart;
use App\Filament\Widgets\ResourceAnalysisChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?int $navigationSort = -2;

    public static function canAccess(): bool
    {
        return true; // Allow all authenticated users to access dashboard
    }

    public function getWidgets(): array
    {
        return [
            SystemOverviewWidget::class,
            AdvancedMetricsWidget::class,
            UsersAnalyticsChart::class,
            UserRegistrationTrendsChart::class,
            ResourceAnalysisChart::class,
            RoleDistributionChart::class,
            UserStatusChart::class,
            EmailVerificationChart::class,
            PermissionsDistributionChart::class,
            LatestUsersWidget::class,
        ];
    }
}
