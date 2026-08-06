<?php

namespace App\Actions\Api\Notification;

use App\Models\User;

class ClearAllNotificationsAction
{
    public function run(User $user, string $type = 'all'): string
    {
        switch ($type) {
            case 'read':
                $count = $user->readNotifications()->count();
                $user->readNotifications()->delete();

                return "{$count} read notifications cleared";

            case 'unread':
                $count = $user->unreadNotifications()->count();
                $user->unreadNotifications()->delete();

                return "{$count} unread notifications cleared";

            case 'all':
            default:
                $count = $user->notifications()->count();
                $user->notifications()->delete();

                return "All {$count} notifications cleared";
        }
    }
}
