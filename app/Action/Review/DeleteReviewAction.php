<?php

namespace App\Action\Review;

class DeleteReviewAction
{
    public function handle($user, int $reviewId): bool
    {
        $review = \App\Models\Review::find($reviewId);
        if (! $review) return false;

        // Only owner or admin can delete
        if ($review->user_id !== $user->id && ! ($user->is_admin ?? false)) {
            throw new \Exception('Unauthorized');
        }

        $review->delete();
        return true;
    }
}
