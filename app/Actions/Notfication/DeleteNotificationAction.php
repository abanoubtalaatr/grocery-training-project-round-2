<?php

namespace App\Actions\Notification;

use Illuminate\Support\Facades\Auth;

class DeleteNotificationAction
{
    public function destroy(string $id)
    {
        Auth::user()
            ->notifications()
            ->findOrFail($id)
            ->delete();
    }

    public function destroyMultiple(array $ids)
    {
        return Auth::user()
            ->notifications()
            ->whereIn('id', $ids)
            ->delete();
    }

    public function clearAll(string $type = 'all')
    {
        $user = Auth::user();

        return match ($type) {
            'read' => $user->readNotifications()->delete(),
            'unread' => $user->unreadNotifications()->delete(),
            default => $user->notifications()->delete(),
        };
    }
}