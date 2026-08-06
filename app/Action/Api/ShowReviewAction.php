<?php

namespace App\Action\Api;

use App\Models\Review;

class ShowReviewAction
{
    public function execute($id)
    {
        return Review::with(['user', 'meal'])->findOrFail($id);
    }
}
