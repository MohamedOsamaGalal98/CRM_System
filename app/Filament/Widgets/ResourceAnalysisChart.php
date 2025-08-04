<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResourceAnalysisChart extends ChartWidget
{
    protected static ?string $heading = 'Resource Activity Analysis';
    protected static ?int $sort = 10;
    
    public ?string $filter = 'monthly';

    protected function getData(): array
    {
        $filter = $this->filter;
        
        switch ($filter) {
            case 'daily':
                return $this->getDailyAnalysis();
            case 'weekly':
                return $this->getWeeklyAnalysis();
            case 'monthly':
                return $this->getMonthlyAnalysis();
            default:
                return $this->getMonthlyAnalysis();
        }
    }

    private function getDailyAnalysis(): array
    {
        $labels = [];
        $usersData = [];
        $rolesData = [];
        $permissionsData = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            
            // Count records created on this day
            $usersData[] = User::whereDate('created_at', $date->toDateString())->count();
            $rolesData[] = Role::whereDate('created_at', $date->toDateString())->count();
            $permissionsData[] = Permission::whereDate('created_at', $date->toDateString())->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $usersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Roles',
                    'data' => $rolesData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Permissions',
                    'data' => $permissionsData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getWeeklyAnalysis(): array
    {
        $labels = [];
        $usersData = [];
        $rolesData = [];
        $permissionsData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
            $labels[] = $startOfWeek->format('M j');
            
            $usersData[] = User::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $rolesData[] = Role::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            $permissionsData[] = Permission::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $usersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Roles',
                    'data' => $rolesData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Permissions',
                    'data' => $permissionsData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getMonthlyAnalysis(): array
    {
        $labels = [];
        $usersData = [];
        $rolesData = [];
        $permissionsData = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $usersData[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $rolesData[] = Role::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            $permissionsData[] = Permission::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $usersData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Roles',
                    'data' => $rolesData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Permissions',
                    'data' => $permissionsData,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'daily' => 'Last 30 days',
            'weekly' => 'Last 12 weeks',
            'monthly' => 'Last 12 months',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
