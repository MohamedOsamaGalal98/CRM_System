<?php

namespace App\Filament\Widgets;

use App\Models\Permission;
use App\Models\Role;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PermissionsDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Permissions by Category';
    protected static ?int $sort = 7;

    protected function getData(): array
    {
        // Group permissions by their category (extracted from permission name)
        $permissions = Permission::all();
        $categories = [];

        foreach ($permissions as $permission) {
            // Extract category from permission name (e.g., "view_users" -> "users")
            $parts = explode('_', $permission->name);
            if (count($parts) >= 2) {
                $category = $parts[1]; // Take the second part as category
                if (!isset($categories[$category])) {
                    $categories[$category] = 0;
                }
                $categories[$category]++;
            }
        }

        // Prepare data for chart
        $labels = array_keys($categories);
        $data = array_values($categories);
        
        $backgroundColors = [
            'rgb(239, 68, 68)',   // red
            'rgb(245, 158, 11)',  // amber
            'rgb(34, 197, 94)',   // green
            'rgb(59, 130, 246)',  // blue
            'rgb(147, 51, 234)',  // purple
            'rgb(236, 72, 153)',  // pink
            'rgb(14, 165, 233)',  // sky
            'rgb(99, 102, 241)',  // indigo
            'rgb(251, 191, 36)',  // yellow
            'rgb(168, 85, 247)',  // violet
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => array_map('ucfirst', $labels), // Capitalize first letter
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
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
