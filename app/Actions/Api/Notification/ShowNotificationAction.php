<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class ShowNotificationAction
{
    public function run(User $user, string $id): DatabaseNotification
    {
        $notification = $user->notifications()->findOrFail($id);

        if (! $notification->read_at) {
            $notification->markAsRead();
        }

        return $notification;
    }
}
