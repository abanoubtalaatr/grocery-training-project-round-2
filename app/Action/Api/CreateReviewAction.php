<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Validation\ValidationException;

class CreateReviewAction
{
    public function execute($user, array $data): Review
    {
        if (Review::hasUserReviewed($user->id, $data['meal_id'])) {
            throw ValidationException::withMessages([
                'meal_id' => ['You have already reviewed this meal'],
            ]);
        }

        return Review::create([
            'user_id' => $user->id,
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ]);
    }
}