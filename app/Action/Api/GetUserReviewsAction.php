<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetUserReviewsAction
{
    public function execute(Request $request)
    {
        $userId = $request->user_id ?? Auth::id();

        $reviews = Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($request->input('per_page', 10));

        return $reviews;
    }
}
