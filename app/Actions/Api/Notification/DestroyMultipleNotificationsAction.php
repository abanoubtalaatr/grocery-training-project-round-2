<?php

namespace App\Actions\Api\Notification;

use App\Models\User;

class DestroyMultipleNotificationsAction
{
    public function run(User $user, array $ids): int
    {
        return $user->notifications()
            ->whereIn('id', $ids)
            ->delete();
    }
}
