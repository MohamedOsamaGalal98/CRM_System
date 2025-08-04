<?php

namespace App\Filament\Widgets;

use App\Models\Role;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RoleDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Role Distribution';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        // Get role distribution data
        $roleData = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name', DB::raw('count(*) as user_count'))
            ->groupBy('roles.id', 'roles.name')
            ->get();

        // Get users without roles
        $usersWithRoles = DB::table('model_has_roles')->distinct('model_id')->count();
        $totalUsers = User::count();
        $usersWithoutRoles = $totalUsers - $usersWithRoles;

        $labels = [];
        $data = [];
        $backgroundColors = [
            'rgb(239, 68, 68)',   // red
            'rgb(245, 158, 11)',  // amber
            'rgb(34, 197, 94)',   // green
            'rgb(59, 130, 246)',  // blue
            'rgb(147, 51, 234)',  // purple
            'rgb(236, 72, 153)',  // pink
            'rgb(14, 165, 233)',  // sky
            'rgb(99, 102, 241)',  // indigo
        ];

        foreach ($roleData as $index => $role) {
            $labels[] = $role->name;
            $data[] = $role->user_count;
        }

        // Add users without roles if any
        if ($usersWithoutRoles > 0) {
            $labels[] = 'No Role Assigned';
            $data[] = $usersWithoutRoles;
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
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
