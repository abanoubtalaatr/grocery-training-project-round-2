<?php

namespace App\Action\Meal;

use App\Models\Meal;

class BestSellsAction
{
    public function handle(int $limit = 10)
    {
        return Meal::with('category')->available()->orderByDesc('sold_count')->limit($limit)->get();
    }
}
