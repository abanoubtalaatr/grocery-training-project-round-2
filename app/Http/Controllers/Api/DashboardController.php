<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\GetDashboardCategoryDistributionAction;
use App\Action\Api\GetDashboardOverviewAction;
use App\Action\Api\GetDashboardRecentOrdersAction;
use App\Action\Api\GetDashboardShoppingInsightsAction;
use App\Action\Api\GetDashboardTopPurchasesAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(
        Request $request,
        GetDashboardOverviewAction $overviewAction,
        GetDashboardShoppingInsightsAction $insightsAction,
        GetDashboardCategoryDistributionAction $distributionAction,
        GetDashboardRecentOrdersAction $recentOrdersAction,
        GetDashboardTopPurchasesAction $topPurchasesAction
    ): JsonResponse {
        $user = $request->user();

        return $this->success(
            [
                'overview' => $overviewAction->execute($user),
                'shopping_insights' => $insightsAction->execute($user),
                'category_distribution' => $distributionAction->execute($user),
                'recent_orders' => $recentOrdersAction->execute($user),
                'top_purchases' => $topPurchasesAction->execute($user),
            ],
            'Dashboard data retrieved successfully'
        );
    }
}
