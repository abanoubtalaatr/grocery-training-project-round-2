<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Dashboard\GetDashboardStatsAction;
use App\Action\Admin\Dashboard\GetRecentActivityAction;
use App\Action\Admin\Dashboard\GetRevenueChartDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(
        Request $request,
        GetDashboardStatsAction $statsAction,
        GetRevenueChartDataAction $chartAction,
        GetRecentActivityAction $activityAction
    ) {
        $stats = $statsAction->execute();
        $chartData = $chartAction->execute('monthly');
        $recentActivity = $activityAction->execute(10);

        return view('admin.dashboard.index', compact('stats', 'chartData', 'recentActivity'));
    }
}
