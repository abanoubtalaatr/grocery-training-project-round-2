<?php

namespace App\Actions\Api\Review;

use App\Models\Review;

class GetMealReviewStatsAction
{
    public function run(int|string $mealId): array
    {
        $stats = Review::where('meal_id', $mealId)
            ->approved()
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            ')
            ->first();

        return [
            'total_reviews' => (int) ($stats->total_reviews ?? 0),
            'average_rating' => round($stats->average_rating ?? 0, 1),
            'rating_distribution' => [
                'five_star' => (int) ($stats->five_star ?? 0),
                'four_star' => (int) ($stats->four_star ?? 0),
                'three_star' => (int) ($stats->three_star ?? 0),
                'two_star' => (int) ($stats->two_star ?? 0),
                'one_star' => (int) ($stats->one_star ?? 0),
            ],
        ];
    }
}
