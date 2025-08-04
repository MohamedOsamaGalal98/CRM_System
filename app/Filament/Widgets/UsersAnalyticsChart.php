<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class UsersAnalyticsChart extends ChartWidget
{
    protected static ?string $heading = 'Users Analytics';
    protected static ?int $sort = 2;
    
    public ?string $filter = 'month';

    protected function getData(): array
    {
        $filter = $this->filter;
        
        switch ($filter) {
            case 'week':
                return $this->getWeeklyData();
            case 'month':
                return $this->getMonthlyData();
            case 'year':
                return $this->getYearlyData();
            default:
                return $this->getMonthlyData();
        }
    }

    private function getWeeklyData(): array
    {
        $labels = [];
        $activeUsers = [];
        $newUsers = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('M j');
            
            // New users on this day
            $newUsers[] = User::whereDate('created_at', $date->toDateString())->count();
            
            // Active users (created before or on this day and still active)
            $activeUsers[] = User::where('created_at', '<=', $date->endOfDay())
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => false,
                ],
                [
                    'label' => 'Active Users',
                    'data' => $activeUsers,
                    'backgroundColor' => 'rgb(16, 185, 129)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getMonthlyData(): array
    {
        $labels = [];
        $activeUsers = [];
        $newUsers = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            // New users in this month
            $newUsers[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            // Active users at the end of this month
            $activeUsers[] = User::where('created_at', '<=', $date->endOfMonth())
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => false,
                ],
                [
                    'label' => 'Active Users',
                    'data' => $activeUsers,
                    'backgroundColor' => 'rgb(16, 185, 129)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getYearlyData(): array
    {
        $labels = [];
        $activeUsers = [];
        $newUsers = [];
        
        for ($i = 4; $i >= 0; $i--) {
            $year = now()->subYears($i)->year;
            $labels[] = (string) $year;
            
            // New users in this year
            $newUsers[] = User::whereYear('created_at', $year)->count();
            
            // Active users at the end of this year
            $activeUsers[] = User::where('created_at', '<=', now()->setYear($year)->endOfYear())
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $newUsers,
                    'backgroundColor' => 'rgb(59, 130, 246)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => false,
                ],
                [
                    'label' => 'Active Users',
                    'data' => $activeUsers,
                    'backgroundColor' => 'rgb(16, 185, 129)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 days',
            'month' => 'Last 12 months',
            'year' => 'Last 5 years',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
