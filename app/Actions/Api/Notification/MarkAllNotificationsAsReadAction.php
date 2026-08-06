<?php

namespace App\Actions\Api\Notification;

use App\Models\User;

class MarkAllNotificationsAsReadAction
{
    public function run(User $user): int
    {
        $unreadCount = $user->unreadNotifications()->count();

        if ($unreadCount === 0) {
            throw new \LogicException('No unread notifications');
        }

        $user->unreadNotifications()->update(['read_at' => now()]);

        return $unreadCount;
    }
}
