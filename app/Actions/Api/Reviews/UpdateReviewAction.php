<?php

namespace App\Actions\Api\Reviews;

use App\Models\Review;

class UpdateReviewAction
{
    public function execute(Review $review, array $data): Review
    {
        $review->update($data);

        return $review->fresh()->load(['user', 'meal']);
    }
}
