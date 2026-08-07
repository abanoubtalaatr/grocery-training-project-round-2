<?php

namespace App\Action\Api;

use App\Models\Review;

class DeleteReviewAction
{
    public function execute($user, string $id): void
    {
        $review = Review::findOrFail($id);

        if ($user->id !== $review->user_id && !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $review->delete();
    }
}