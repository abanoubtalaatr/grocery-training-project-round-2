<?php

namespace App\Action\Dashboard;

class DashboardPresenter
{
    public function present(array $overview, array $insights, array $distribution, array $recentOrders, array $topPurchases): array
    {
        return [
            'overview' => $overview,
            'shopping_insights' => $insights,
            'category_distribution' => $distribution,
            'recent_orders' => $recentOrders,
            'top_purchases' => $topPurchases,
        ];
    }
}
