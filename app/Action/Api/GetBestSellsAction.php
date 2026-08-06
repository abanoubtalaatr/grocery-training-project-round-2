<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetBestSellsAction
{
    public function execute()
    {
        return Meal::with('category')->available()->take(10)->get();
    }
}
