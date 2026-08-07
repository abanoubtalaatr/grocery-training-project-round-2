<?php

namespace App\Actions\Api\Reviews;

use App\Models\Review;
use App\Models\User;
use InvalidArgumentException;

class StoreReviewAction
{
    public function execute(User $user, array $data): Review
    {
        if (Review::hasUserReviewed($user->id, $data['meal_id'])) {
            throw new InvalidArgumentException('You have already reviewed this meal');
        }

        return Review::create([
            'user_id' => $user->id,
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ])->load(['user', 'meal']);
    }
}
