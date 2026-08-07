<?php

namespace App\Actions\Review;

use App\Models\Review;

class UpdateReviewAction
{
    public function execute(Review $review, array $validated): Review
    {
        $review->update($validated);

        return $review->fresh(['user', 'meal']);
    }
}
