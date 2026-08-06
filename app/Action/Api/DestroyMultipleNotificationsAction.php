<?php

namespace App\Action\Api;

class DestroyMultipleNotificationsAction
{
    public function execute($user, array $ids): int
    {
        $deletedCount = $user->notifications()
            ->whereIn('id', $ids)
            ->delete();

        return $deletedCount;
    }
}
