<?php

namespace App\Actions\Api\Review;

use App\Models\Review;
use App\Models\User;
use LogicException;

class StoreReviewAction
{
    public function run(User $user, array $data): Review
    {
        if (Review::hasUserReviewed($user->id, $data['meal_id'])) {
            throw new LogicException('You have already reviewed this meal');
        }

        $review = Review::create([
            'user_id' => $user->id,
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ]);

        return $review->load(['user', 'meal']);
    }
}
