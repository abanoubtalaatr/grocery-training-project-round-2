<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetMealSliderAction
{
    public function execute()
    {
        return Meal::with('category')
            ->available()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}