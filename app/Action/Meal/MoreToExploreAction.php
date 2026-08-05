<?php

namespace App\Action\Meal;

use App\Models\Meal;

class MoreToExploreAction
{
    public function handle()
    {
        return Meal::with('category')->available()->orderBy('created_at', 'desc')->get();
    }
}
