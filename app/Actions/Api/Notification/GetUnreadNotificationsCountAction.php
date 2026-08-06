<?php

namespace App\Actions\Api\Notification;

use App\Models\User;

class GetUnreadNotificationsCountAction
{
    public function run(User $user): array
    {
        $count = $user->unreadNotifications()->count();

        return [
            'count' => $count,
            'has_unread' => $count > 0,
        ];
    }
}
