<?php

namespace App\Actions\Api\Review;

use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DestroyReviewAction
{
    public function run(User $user, int|string $id): bool
    {
        $review = Review::findOrFail($id);

        if ($user->id !== $review->user_id && ! $user->is_admin) {
            throw new AuthorizationException('Unauthorized');
        }

        return (bool) $review->delete();
    }
}
