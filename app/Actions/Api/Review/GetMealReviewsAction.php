<?php

namespace App\Actions\Api\Review;

use App\Models\Meal;
use App\Models\Review;

class GetMealReviewsAction
{
    public function run(int|string $mealId, int $perPage = 10): array
    {
        $meal = Meal::findOrFail($mealId);

        $reviews = Review::with('user')
            ->where('meal_id', $mealId)
            ->approved()
            ->latest()
            ->paginate($perPage);

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
