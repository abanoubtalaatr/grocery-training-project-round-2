<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class MarkNotificationAsUnreadAction
{
    public function run(User $user, string $id): DatabaseNotification
    {
        $notification = $user->notifications()->findOrFail($id);

        if (! $notification->read_at) {
            throw new \LogicException('Notification is already unread');
        }

        $notification->markAsUnread();

        return $notification;
    }
}
