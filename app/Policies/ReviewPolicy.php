<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function update(User $user, Review $review): bool
    {
        return (string) $user->id === (string) $review->user_id || (bool) $user->is_admin;
    }

    public function delete(User $user, Review $review): bool
    {
        return (string) $user->id === (string) $review->user_id || (bool) $user->is_admin;
    }
}
