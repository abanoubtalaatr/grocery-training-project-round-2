<?php

namespace App\Http\Controllers\Dashboard;

use App\Actions\Admin\SmartList\IndexSmartListAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartListController extends Controller
{
    public function index(Request $request, IndexSmartListAction $action): View
    {
        $smartLists = $action->run($request);

        return view('admin.smart-lists.index', compact('smartLists'));
    }
}
