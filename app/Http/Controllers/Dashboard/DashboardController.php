<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Dashboard\GetDashboardStatsAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected $statsAction;

    public function __construct(GetDashboardStatsAction $statsAction)
    {
        $this->statsAction = $statsAction;
    }

    /**
     * Display the dashboard.
     */
    public function index(): View
    {
        $stats = $this->statsAction->run();

        return view('admin.dashboard', $stats);
    }
}
