<?php

namespace App\Actions\Review;

use App\Models\Review;
use App\Models\User;
use Exception;

class StoreReviewAction
{
    public function execute(User $user, array $validated): Review
    {
        if (Review::hasUserReviewed($user->id, $validated['meal_id'])) {
            throw new Exception('You have already reviewed this meal');
        }

        return Review::create([
            'user_id'     => $user->id,
            'meal_id'     => $validated['meal_id'],
            'rating'      => $validated['rating'],
            'comment'     => $validated['comment'] ?? null,
            'images'      => $validated['images'] ?? null,
            'is_approved' => false,
        ]);
    }
}
