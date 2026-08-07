<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Meal;
use App\Models\Category;
use App\Actions\Api\Dashboard\IndexDashboardAction;
use App\Actions\Api\Dashboard\StoreDashboardAction;
use App\Actions\Api\Dashboard\ShowDashboardAction;
use App\Actions\Api\Dashboard\UpdateDashboardAction;
use App\Actions\Api\Dashboard\DestroyDashboardAction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private IndexDashboardAction $indexAction;
    private StoreDashboardAction $storeAction;
    private ShowDashboardAction $showAction;
    private UpdateDashboardAction $updateAction;
    private DestroyDashboardAction $destroyAction;
    public function __construct(
        IndexDashboardAction $indexAction,
        StoreDashboardAction $storeAction,
        ShowDashboardAction $showAction,
        UpdateDashboardAction $updateAction,
        DestroyDashboardAction $destroyAction
    ) {
        $this->indexAction = $indexAction;
        $this->storeAction = $storeAction;
        $this->showAction = $showAction;
        $this->updateAction = $updateAction;
        $this->destroyAction = $destroyAction;
    }

    public function index(Request $request): JsonResponse
    {
        return $this->indexAction->handle($request);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->storeAction->handle($request);
    }

    public function show($id): JsonResponse
    {
        return $this->showAction->handle($id);
    }

    public function update($id, Request $request): JsonResponse
    {
        return $this->updateAction->handle($id, $request);
    }

    public function destroy($id): JsonResponse
    {
        return $this->destroyAction->handle($id);
    }
}
