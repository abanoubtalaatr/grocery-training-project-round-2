<?php

namespace App\Action\Api;

use App\Models\Meal;

class GetHotMealsAction
{
    public function execute()
    {
        return Meal::with('category')
            ->available()
            ->hot()
            ->orderBy('created_at', 'desc')
            ->get();
    }
}