<?php

namespace App\Filament\Resources\CustomFieldResource\Widgets;

use App\Models\CustomField;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CustomFieldStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCustomFields = CustomField::count();
        $activeCustomFields = CustomField::where('is_active', true)->count();
        $deletedCustomFields = CustomField::onlyTrashed()->count();

        // Active rate
        $activeRate = $totalCustomFields > 0 ? round(($activeCustomFields / $totalCustomFields) * 100, 1) : 0;

        // Most recent custom field
        $recentCustomField = CustomField::orderBy('created_at', 'desc')->first();

        return [
            Stat::make('Total Custom Fields', $totalCustomFields)
                ->description($activeCustomFields . ' active custom fields')
                ->descriptionIcon('heroicon-m-adjustments-horizontal')
                ->color('primary'),

            Stat::make('Active Rate', $activeRate . '%')
                ->description($activeCustomFields . ' of ' . $totalCustomFields . ' are active')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($activeRate >= 80 ? 'success' : ($activeRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Most Recent', $recentCustomField ? substr($recentCustomField->name, 0, 15) . (strlen($recentCustomField->name) > 15 ? '...' : '') : 'N/A')
                ->description($recentCustomField ? $recentCustomField->created_at->diffForHumans() : 'No custom fields')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Deleted Custom Fields', $deletedCustomFields)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}