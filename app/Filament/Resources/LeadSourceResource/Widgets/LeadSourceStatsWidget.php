<?php

namespace App\Filament\Resources\LeadSourceResource\Widgets;

use App\Models\LeadSource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadSourceStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLeadSources = LeadSource::count();
        $activeLeadSources = LeadSource::where('is_active', true)->count();
        $deletedLeadSources = LeadSource::onlyTrashed()->count();

        // Active rate
        $activeRate = $totalLeadSources > 0 ? round(($activeLeadSources / $totalLeadSources) * 100, 1) : 0;

        // Most recent lead source
        $recentLeadSource = LeadSource::orderBy('created_at', 'desc')->first();

        return [
            Stat::make('Total Lead Sources', $totalLeadSources)
                ->description($activeLeadSources . ' active lead sources')
                ->descriptionIcon('heroicon-m-funnel')
                ->color('primary'),

            Stat::make('Active Rate', $activeRate . '%')
                ->description($activeLeadSources . ' of ' . $totalLeadSources . ' are active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($activeRate >= 80 ? 'success' : ($activeRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Most Recent', $recentLeadSource ? substr($recentLeadSource->name, 0, 15) . (strlen($recentLeadSource->name) > 15 ? '...' : '') : 'N/A')
                ->description($recentLeadSource ? $recentLeadSource->created_at->diffForHumans() : 'No lead sources')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Deleted Lead Sources', $deletedLeadSources)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}