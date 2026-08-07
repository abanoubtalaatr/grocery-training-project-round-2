<?php

namespace App\Action\Admin\Dashboard;

use App\Models\Order;
use Carbon\Carbon;

class GetRevenueChartDataAction
{
    public function execute(string $period = 'monthly'): array
    {
        return match ($period) {
            'weekly' => $this->getWeeklyData(),
            'yearly' => $this->getYearlyData(),
            default => $this->getMonthlyData(),
        };
    }

    private function getMonthlyData(): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $month = $date->format('Y-m');
            
            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total');

            $ordersCount = Order::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = [
                'label' => $date->format('M Y'),
                'revenue' => round($revenue, 2),
                'orders' => $ordersCount,
            ];
        }

        return $data;
    }

    private function getWeeklyData(): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 7; $i >= 0; $i--) {
            $date = $now->copy()->subWeeks($i);
            $startOfWeek = $date->copy()->startOfWeek();
            $endOfWeek = $date->copy()->endOfWeek();

            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->sum('total');

            $ordersCount = Order::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->count();

            $data[] = [
                'label' => $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d'),
                'revenue' => round($revenue, 2),
                'orders' => $ordersCount,
            ];
        }

        return $data;
    }

    private function getYearlyData(): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 4; $i >= 0; $i--) {
            $year = $now->copy()->subYears($i)->year;

            $revenue = Order::where('status', '!=', 'cancelled')
                ->whereYear('created_at', $year)
                ->sum('total');

            $ordersCount = Order::whereYear('created_at', $year)
                ->count();

            $data[] = [
                'label' => (string) $year,
                'revenue' => round($revenue, 2),
                'orders' => $ordersCount,
            ];
        }

        return $data;
    }
}
