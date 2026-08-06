<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class DeleteReviewAction
{
    public function execute($id)
    {
        $review = Review::findOrFail($id);

        if (Auth::id() !== $review->user_id && ! Auth::user()->is_admin) {
            return ['success' => false, 'status' => 403, 'message' => 'Unauthorized'];
        }

        $review->delete();

        return ['success' => true];
    }
}
