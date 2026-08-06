<?php

namespace App\Actions\Api\Review;

use App\Models\Review;

class ShowReviewAction
{
    public function run(int|string $id): Review
    {
        return Review::with(['user', 'meal'])->findOrFail($id);
    }
}
