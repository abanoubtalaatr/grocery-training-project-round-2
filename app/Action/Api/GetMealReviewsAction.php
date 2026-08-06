<?php

namespace App\Action\Api;

use App\Models\Meal;
use App\Models\Review;

class GetMealReviewsAction
{
    public function execute(string $mealId, int $perPage = 10): array
    {
        $meal = Meal::findOrFail($mealId);

        $reviews = Review::with('user')
            ->where('meal_id', $mealId)
            ->approved()
            ->latest()
            ->paginate($perPage);

        return [
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'average_rating' => round(Review::getAverageRating($mealId), 1),
                'total_reviews' => Review::getTotalReviews($mealId),
            ],
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ];
    }
}