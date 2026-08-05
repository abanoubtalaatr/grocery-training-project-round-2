<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Dashboard\GetOverviewAction;
use App\Action\Dashboard\GetShoppingInsightsAction;
use App\Action\Dashboard\GetCategoryDistributionAction;
use App\Action\Dashboard\GetRecentOrdersAction;
use App\Action\Dashboard\GetTopPurchasesAction;
use App\Action\Dashboard\DashboardPresenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    private int $cacheTtl;

    public function __construct()
    {
        $this->cacheTtl = 60; // seconds
    }

    public function index(Request $request,
                          GetOverviewAction $overviewAction,
                          GetShoppingInsightsAction $insightsAction,
                          GetCategoryDistributionAction $distributionAction,
                          GetRecentOrdersAction $recentOrdersAction,
                          GetTopPurchasesAction $topPurchasesAction,
                          DashboardPresenter $presenter): JsonResponse
    {
        try {
            $user = $request->user();
            if (! $user) {
                return response()->json(['success' => false, 'message' => 'Authentication required'], 401);
            }

            $cacheKey = 'dashboard:user:' . $user->id;

            $payload = cache()->remember($cacheKey, $this->cacheTtl, function () use ($user, $overviewAction, $insightsAction, $distributionAction, $recentOrdersAction, $topPurchasesAction, $presenter) {
                $overview = $overviewAction->handle($user);
                $insights = $insightsAction->handle($user);
                $distribution = $distributionAction->handle($user);
                $recentOrders = $recentOrdersAction->handle($user);
                $topPurchases = $topPurchasesAction->handle($user);

                return $presenter->present($overview, $insights, $distribution, $recentOrders, $topPurchases);
            });

            return response()->json(['success' => true, 'message' => 'Dashboard data retrieved successfully', 'data' => $payload]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve dashboard data', 'error' => $e->getMessage()], 500);
        }
    }
}
