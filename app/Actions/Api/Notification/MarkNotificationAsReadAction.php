<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class MarkNotificationAsReadAction
{
    public function run(User $user, string $id): DatabaseNotification
    {
        $notification = $user->notifications()->findOrFail($id);

        if ($notification->read_at) {
            throw new \LogicException('Notification is already read');
        }

        $notification->markAsRead();

        return $notification;
    }
}
