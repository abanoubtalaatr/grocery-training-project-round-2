<?php

namespace App\Actions\Review;

use App\Models\Review;

class DeleteReviewAction
{
    public function execute(Review $review): void
    {
        $review->delete();
    }
}
