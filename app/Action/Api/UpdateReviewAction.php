<?php

namespace App\Action\Api;

use App\Models\Review;

class UpdateReviewAction
{
    public function execute($user, string $id, array $data): Review
    {
        $review = Review::findOrFail($id);

        // Check authorization
        if ($user->id !== $review->user_id && !$user->is_admin) {
            abort(403, 'Unauthorized');
        }

        $review->update($data);

        return $review;
    }
}