<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class UserStatusChart extends ChartWidget
{
    protected static ?string $heading = 'User Status Distribution';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();
        $deletedUsers = User::onlyTrashed()->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $unverifiedUsers = User::whereNull('email_verified_at')->count();

        return [
            'datasets' => [
                [
                    'label' => 'User Status',
                    'data' => [$activeUsers, $inactiveUsers, $deletedUsers],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // green for active
                        'rgb(245, 158, 11)',  // amber for inactive
                        'rgb(239, 68, 68)',   // red for deleted
                    ],
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => ['Active Users', 'Inactive Users', 'Deleted Users'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ": " + context.parsed + " (" + percentage + "%)";
                        }'
                    ]
                ]
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
