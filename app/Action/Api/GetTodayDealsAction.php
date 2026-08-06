<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetTodayDealsAction
{
    public function execute()
    {
        return Meal::with('category')->available()->withActiveDiscount()->orderBy('created_at', 'desc')->get();
    }
}
