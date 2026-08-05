<?php

namespace App\Action\Review;

use App\Models\Review;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreateReviewAction
{
    public function handle($user, array $data)
    {
        // Ensure meal exists
        $meal = \App\Models\Meal::find($data['meal_id']);
        if (! $meal) throw new ModelNotFoundException('Meal not found');

        $review = Review::create([
            'user_id' => $user->id,
            'meal_id' => $data['meal_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'images' => $data['images'] ?? null,
            'approved' => false,
        ]);

        return $review;
    }
}
