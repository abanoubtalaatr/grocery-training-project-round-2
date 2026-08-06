<?php

namespace App\Action\Api;

class MarkNotificationAction
{
    public function execute($user, string $id, string $mode = 'read')
    {
        $notification = $user->notifications()->findOrFail($id);

        if ($mode === 'read') {
            if (! $notification->read_at) {
                $notification->markAsRead();
            }

            return ['changed' => true, 'notification' => $this->transform($notification)];
        }

        if ($mode === 'unread') {
            if ($notification->read_at) {
                $notification->markAsUnread();
            }

            return ['changed' => true, 'notification' => $this->transform($notification)];
        }

        return ['changed' => false, 'notification' => $this->transform($notification)];
    }

    private function transform($notification)
    {
        return [
            'id' => $notification->id,
            'is_read' => ! is_null($notification->read_at),
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
