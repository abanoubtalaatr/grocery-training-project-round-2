<?php

namespace App\Action\Api;

class MarkAllNotificationsAsReadAction
{
    public function execute($user): int
    {
        $unreadCount = $user->unreadNotifications()->count();

        if ($unreadCount > 0) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }

        return $unreadCount;
    }
}
