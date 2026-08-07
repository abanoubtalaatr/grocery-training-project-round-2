<?php

namespace App\Actions\Api\Review;

use App\Http\Requests\Api\UpdateReviewRequest;
use App\Models\Review;

class UpdateReviewAction
{
    /**
     * Execute the action.
     */
    public function execute(Review $review, UpdateReviewRequest $request): Review
    {
        $validated = $request->validated();

        $review->update($validated);

        return $review->load(['user', 'meal']);
    }
}
