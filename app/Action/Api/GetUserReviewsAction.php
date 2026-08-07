<?php

namespace App\Action\Api;

use App\Models\Review;

class GetUserReviewsAction
{
    public function execute(int $userId, int $perPage = 10): array
    {
        $reviews = Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);

        return [
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