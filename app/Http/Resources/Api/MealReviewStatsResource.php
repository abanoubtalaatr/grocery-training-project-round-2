<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealReviewStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'total_reviews' => (int) $data['total_reviews'],
            'average_rating' => round($data['average_rating'] ?? 0, 1),
            'rating_distribution' => [
                'five_star' => (int) $data['five_star'],
                'four_star' => (int) $data['four_star'],
                'three_star' => (int) $data['three_star'],
                'two_star' => (int) $data['two_star'],
                'one_star' => (int) $data['one_star'],
            ],
        ];
    }
}