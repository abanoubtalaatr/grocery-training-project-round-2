<?php

namespace App\Actions\Api\Review;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class UpdateReviewAction
{
    public function run(User $user, int|string $id, array $data): Review
    {
        $review = Review::findOrFail($id);

        if ($user->id !== $review->user_id && ! $user->is_admin) {
            throw new AuthorizationException('Unauthorized');
        }

        $review->update($data);

        return $review->load(['user', 'meal']);
    }
}
