<?php

namespace App\Action\Api;

class DeleteMultipleNotificationsAction
{
    public function execute($user, array $ids): int
    {
        return $user->notifications()->whereIn('id', $ids)->delete();
    }
}