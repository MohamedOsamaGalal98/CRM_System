<?php

namespace App\Filament\Resources\LabelResource\Widgets;

use App\Models\Label;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LabelStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalLabels = Label::count();
        $activeLabels = Label::where('is_active', true)->count();
        $deletedLabels = Label::onlyTrashed()->count();

        // Labels with customers
        $labelsWithCustomers = Label::has('customers')->count();
        $usageRate = $totalLabels > 0 ? round(($labelsWithCustomers / $totalLabels) * 100, 1) : 0;

        // Most used label
        $popularLabel = Label::withCount('customers')
            ->orderBy('customers_count', 'desc')
            ->first();

        // Color distribution
        $colorCount = Label::distinct('color')->count('color');

        return [
            Stat::make('Total Labels', $totalLabels)
                ->description($activeLabels . ' active labels')
                ->descriptionIcon('heroicon-m-tag')
                ->color('primary'),

            Stat::make('Usage Rate', $usageRate . '%')
                ->description($labelsWithCustomers . ' labels assigned to customers')
                ->descriptionIcon('heroicon-m-link')
                ->color($usageRate >= 50 ? 'success' : ($usageRate >= 25 ? 'warning' : 'danger')),

            Stat::make('Most Used Label', $popularLabel ? substr($popularLabel->name, 0, 15) . '...' : 'N/A')
                ->description($popularLabel ? $popularLabel->customers_count . ' customers' : 'No assignments')
                ->descriptionIcon('heroicon-m-star')
                ->color('warning'),

            Stat::make('Color Variations', $colorCount)
                ->description('Different label colors')
                ->descriptionIcon('heroicon-m-swatch')
                ->color('info'),

            Stat::make('Deleted Labels', $deletedLabels)
                ->description('Soft deleted records')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}