<?php

namespace App\Actions\Api\Review;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetReviewsAction
{
    public function run(Request $request): LengthAwarePaginator
    {
        $query = Review::query()
            ->with(['user', 'meal'])
            ->latest();

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

        $perPage = (int) $request->input('per_page', 15);

        return $query->paginate($perPage);
    }
}
