<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Monitoring\GetSystemRisksAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function index(GetSystemRisksAction $action): View
    {
        $data = $action->run();

        return view('admin.monitoring.index', $data);
    }
}
