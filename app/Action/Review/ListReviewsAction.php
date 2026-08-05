<?php

namespace App\Action\Review;

use App\Models\Review;

class ListReviewsAction
{
    public function handle(int $mealId, int $perPage = 10)
    {
        return Review::where('meal_id', $mealId)->approved()->with('user:id,username,firstname,lastname')->orderBy('created_at', 'desc')->paginate(min(max($perPage,1),50));
    }
}
