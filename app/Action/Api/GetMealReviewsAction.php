<?php

namespace App\Action\Api;

use App\Models\Review;
use App\Models\Meal;
use Illuminate\Http\Request;

class GetMealReviewsAction
{
    public function execute($mealId, Request $request)
    {
        $meal = Meal::findOrFail($mealId);

        $reviews = Review::with('user')
            ->where('meal_id', $mealId)
            ->approved()
            ->latest()
            ->paginate($request->input('per_page', 10));

        $averageRating = Review::getAverageRating($mealId);
        $totalReviews = Review::getTotalReviews($mealId);

        return [
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
            ],
            'reviews' => $reviews,
        ];
    }
}
