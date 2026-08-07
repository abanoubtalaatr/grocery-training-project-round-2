<?php

namespace App\Action\Admin\Product;

use App\Models\Meal;

class GetProductStatsAction
{
    public function execute(): array
    {
        return [
            'total' => Meal::count(),
            'active' => Meal::where('is_available', true)->count(),
            'inactive' => Meal::where('is_available', false)->count(),
            'featured' => Meal::where('is_featured', true)->count(),
            'out_of_stock' => Meal::where('stock_quantity', 0)->count(),
            'low_stock' => Meal::where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 5)->count(),
            'average_price' => round(Meal::avg('price') ?? 0, 2),
            'total_sold' => Meal::sum('sold_count'),
        ];
    }
}
