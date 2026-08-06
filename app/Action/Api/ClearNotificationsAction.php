<?php

namespace App\Action\Api;

class ClearNotificationsAction
{
    public function execute($user, string $type = 'all'): int
    {
        switch ($type) {
            case 'read':
                $count = $user->readNotifications()->count();
                $user->readNotifications()->delete();
                break;

            case 'unread':
                $count = $user->unreadNotifications()->count();
                $user->unreadNotifications()->delete();
                break;

            case 'all':
            default:
                $count = $user->notifications()->count();
                $user->notifications()->delete();
                break;
        }

        return $count;
    }
}
