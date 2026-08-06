<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class UpdateReviewAction
{
    public function execute($id, array $validated)
    {
        $review = Review::findOrFail($id);

        if (Auth::id() !== $review->user_id && ! Auth::user()->is_admin) {
            return ['success' => false, 'status' => 403, 'message' => 'Unauthorized'];
        }

        $review->update($validated);

        return ['success' => true, 'review' => $review->load(['user', 'meal'])];
    }
}
