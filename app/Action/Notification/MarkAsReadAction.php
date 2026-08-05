<?php

namespace App\Action\Notification;

class MarkAsReadAction
{
    public function handle($user, string $notificationId): bool
    {
        if (!method_exists($user, 'unreadNotifications')) return false;

        $notif = $user->unreadNotifications()->where('id', $notificationId)->first();
        if (! $notif) return false;

        $notif->markAsRead();
        return true;
    }
}
