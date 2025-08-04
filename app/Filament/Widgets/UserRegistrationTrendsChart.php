<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserRegistrationTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'User Registration Trends';
    protected static ?int $sort = 9;
    
    public ?string $filter = 'daily';

    protected function getData(): array
    {
        $filter = $this->filter;
        
        switch ($filter) {
            case 'daily':
                return $this->getDailyTrends();
            case 'weekly':
                return $this->getWeeklyTrends();
            case 'monthly':
                return $this->getMonthlyTrends();
            default:
                return $this->getDailyTrends();
        }
    }

    private function getDailyTrends(): array
    {
        $labels = [];
        $registrations = [];
        $activeRegistrations = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            
            // Total registrations on this day
            $registrations[] = User::whereDate('created_at', $date->toDateString())->count();
            
            // Active registrations (users who registered and are still active)
            $activeRegistrations[] = User::whereDate('created_at', $date->toDateString())
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Registrations',
                    'data' => $registrations,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Active Registrations',
                    'data' => $activeRegistrations,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getWeeklyTrends(): array
    {
        $labels = [];
        $registrations = [];
        $activeRegistrations = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
            $labels[] = $startOfWeek->format('M j') . ' - ' . $endOfWeek->format('M j');
            
            // Total registrations in this week
            $registrations[] = User::whereBetween('created_at', [$startOfWeek, $endOfWeek])->count();
            
            // Active registrations in this week
            $activeRegistrations[] = User::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Registrations',
                    'data' => $registrations,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Active Registrations',
                    'data' => $activeRegistrations,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    private function getMonthlyTrends(): array
    {
        $labels = [];
        $registrations = [];
        $activeRegistrations = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            // Total registrations in this month
            $registrations[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            // Active registrations in this month
            $activeRegistrations[] = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('is_active', true)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Registrations',
                    'data' => $registrations,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Active Registrations',
                    'data' => $activeRegistrations,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
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
