<?php

namespace App\Actions\Api\Review;

use App\Http\Requests\Api\StoreReviewRequest;
use App\Models\Review;

class StoreReviewAction
{
    /**
     * Execute the action.
     */
    public function execute(StoreReviewRequest $request): Review
    {
        $validated = $request->validated();

        $review = Review::create([
            'user_id' => $request->user()->id,
            'meal_id' => $validated['meal_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'images' => $validated['images'] ?? null,
            'is_approved' => false, // Admin approval required
        ]);

        return $review->load(['user', 'meal']);
    }
}
