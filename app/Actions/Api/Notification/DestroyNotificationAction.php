<?php

namespace App\Actions\Api\Notification;

use App\Models\User;

class DestroyNotificationAction
{
    public function run(User $user, string $id): bool
    {
        $notification = $user->notifications()->findOrFail($id);

        return (bool) $notification->delete();
    }
}
