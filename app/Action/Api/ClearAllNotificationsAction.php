<?php

namespace App\Action\Api;

class ClearAllNotificationsAction
{
    public function execute($user, string $type = 'all'): array
    {
        switch ($type) {
            case 'read':
                $count = $user->readNotifications()->count();
                $user->readNotifications()->delete();
                $message = "{$count} read notifications cleared";
                break;

            case 'unread':
                $count = $user->unreadNotifications()->count();
                $user->unreadNotifications()->delete();
                $message = "{$count} unread notifications cleared";
                break;

            case 'all':
            default:
                $count = $user->notifications()->count();
                $user->notifications()->delete();
                $message = "All {$count} notifications cleared";
                break;
        }

        return [
            'count' => $count,
            'message' => $message,
        ];
    }
}