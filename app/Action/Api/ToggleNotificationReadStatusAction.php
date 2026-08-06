<?php

namespace App\Action\Api;

class ToggleNotificationReadStatusAction
{
    public function markAsRead($user, string $id)
    {
        $notification = $user->notifications()->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $notification;
    }

    public function markAsUnread($user, string $id)
    {
        $notification = $user->notifications()->findOrFail($id);

        if ($notification->read_at) {
            $notification->markAsUnread();
        }

        return $notification;
    }
}