<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\GetAdminDashboardDataAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected GetAdminDashboardDataAction $dashboardAction)
    {
    }

    public function index(Request $request): View
    {
        $data = $this->dashboardAction->execute();

        return view('admin.dashboard.index', [
            'data' => $data,
            'user' => $request->user(),
        ]);
    }
}
