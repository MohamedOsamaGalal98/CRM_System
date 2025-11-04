<?php

namespace App\Filament\Resources\StatusResource\Widgets;

use App\Models\Status;
use App\Models\CustomerStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatusStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalStatuses = Status::count();
        $activeStatuses = Status::where('is_active', true)->count();
        $defaultStatus = Status::where('is_default', true)->first();
        $deletedStatuses = Status::onlyTrashed()->count();

        // Status usage statistics
        $usedStatuses = CustomerStatus::distinct('status_id')->count();
        $usageRate = $totalStatuses > 0 ? round(($usedStatuses / $totalStatuses) * 100, 1) : 0;

        // Most used status
        $popularStatus = CustomerStatus::select('status_id')
            ->selectRaw('count(*) as customer_count')
            ->groupBy('status_id')
            ->orderBy('customer_count', 'desc')
            ->first();

        $popularStatusName = null;
        $popularStatusCount = 0;
        if ($popularStatus) {
            $status = Status::find($popularStatus->status_id);
            $popularStatusName = $status ? $status->name : null;
            $popularStatusCount = $popularStatus->customer_count;
        }

        // Position range
        $maxPosition = Status::max('position');
        $minPosition = Status::min('position');

        return [
            Stat::make('Total Statuses', $totalStatuses)
                ->description($activeStatuses . ' active statuses')
                ->descriptionIcon('heroicon-m-flag')
                ->color('primary'),

            Stat::make('Usage Rate', $usageRate . '%')
                ->description($usedStatuses . ' statuses assigned to customers')
                ->descriptionIcon('heroicon-m-link')
                ->color($usageRate >= 80 ? 'success' : ($usageRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Default Status', $defaultStatus ? substr($defaultStatus->name, 0, 15) . '...' : 'Not Set')
                ->description($defaultStatus ? 'Position: ' . $defaultStatus->position : 'No default status')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Most Used Status', $popularStatusName ? substr($popularStatusName, 0, 15) . '...' : 'N/A')
                ->description($popularStatusCount ? $popularStatusCount . ' customers' : 'No assignments')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Position Range', $minPosition . ' - ' . $maxPosition)
                ->description('Status ordering positions')
                ->descriptionIcon('heroicon-m-bars-3')
                ->color('info'),

            Stat::make('Deleted Statuses', $deletedStatuses)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}