<?php

namespace App\Action\Api;

class DeleteNotificationAction
{
    public function execute($user, string $id): void
    {
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();
    }
}
