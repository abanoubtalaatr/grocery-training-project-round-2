<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class CreateReviewAction
{
    public function execute(array $data)
    {
        if (Review::hasUserReviewed(Auth::id(), $data['meal_id'])) {
            return ['success' => false, 'message' => 'You have already reviewed this meal'];
        }

        $review = Review::create([
            'user_id' => Auth::id(),
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'is_approved' => false,
        ]);

        return ['success' => true, 'review' => $review->load(['user', 'meal'])];
    }
}
