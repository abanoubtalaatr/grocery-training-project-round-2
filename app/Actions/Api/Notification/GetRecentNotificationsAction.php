<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetRecentNotificationsAction
{
    public function run(User $user): Collection
    {
        return $user->notifications()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }
}
