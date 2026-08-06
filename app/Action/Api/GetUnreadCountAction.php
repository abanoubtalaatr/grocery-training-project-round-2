<?php

namespace App\Action\Api;

class GetUnreadCountAction
{
    public function execute($user): int
    {
        return $user->unreadNotifications()->count();
    }
}