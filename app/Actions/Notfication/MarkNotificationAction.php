<?php

namespace App\Actions\Notification;

use Illuminate\Support\Facades\Auth;

class MarkNotificationAction
{
    public function read(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $notification;
    }

    public function unread(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        if ($notification->read_at) {
            $notification->markAsUnread();
        }

        return $notification;
    }

    public function readAll()
    {
        $user = Auth::user();

        $count = $user->unreadNotifications()->count();

        $user->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return $count;
    }
}