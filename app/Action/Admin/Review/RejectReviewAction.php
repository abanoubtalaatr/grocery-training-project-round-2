<?php

namespace App\Action\Admin\Review;

use App\Models\Review;

class RejectReviewAction
{
    public function execute(Review $review): void
    {
        $review->update([
            'is_approved' => false,
        ]);
    }
}
