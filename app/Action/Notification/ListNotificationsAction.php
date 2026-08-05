<?php

namespace App\Action\Notification;

class ListNotificationsAction
{
    public function handle($user, int $perPage = 15)
    {
        if (method_exists($user, 'notifications')) {
            return $user->notifications()->paginate(min(max($perPage,1),50));
        }

        return collect([]);
    }
}
