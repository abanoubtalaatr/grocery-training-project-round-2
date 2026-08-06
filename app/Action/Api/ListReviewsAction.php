<?php

namespace App\Action\Api;

use App\Models\Review;
use Illuminate\Http\Request;

class ListReviewsAction
{
    public function execute(Request $request)
    {
        $query = Review::query()->with(['user', 'meal'])->latest();

        if ($request->has('meal_id')) {
            $query->where('meal_id', $request->meal_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->boolean('approved_only', true)) {
            $query->approved();
        }

        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        $perPage = $request->input('per_page', 15);

        return $query->paginate($perPage);
    }
}
