<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\Analytics\GetAnalyticsDataAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(GetAnalyticsDataAction $action): View
    {
        $data = $action->run();

        return view('admin.analytics.index', $data);
    }
}
