<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class EmailVerificationChart extends ChartWidget
{
    protected static ?string $heading = 'Email Verification Status';
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $unverifiedUsers = User::whereNull('email_verified_at')->count();

        return [
            'datasets' => [
                [
                    'data' => [$verifiedUsers, $unverifiedUsers],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',   // green for verified
                        'rgb(239, 68, 68)',   // red for unverified
                    ],
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => ['Verified', 'Unverified'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
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
            'cutout' => '60%',
        ];
    }
}
