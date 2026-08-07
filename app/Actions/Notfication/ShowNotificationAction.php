<?php

namespace App\Actions\Notification;

use Illuminate\Support\Facades\Auth;

class ShowNotificationAction
{
    public function execute(string $id)
    {
        $notification = Auth::user()
            ->notifications()
            ->findOrFail($id);

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        return $notification;
    }
}