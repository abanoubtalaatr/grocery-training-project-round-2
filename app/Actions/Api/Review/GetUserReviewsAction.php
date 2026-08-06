<?php

namespace App\Actions\Api\Review;

use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;

class GetUserReviewsAction
{
    public function run(int|string $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::with('meal')
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }
}
