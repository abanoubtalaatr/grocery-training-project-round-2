<?php

namespace App\Action\Api;

class ListNotificationsByTypeAction
{
    public function execute($user, string $type, int $perPage = 15)
    {
        $notifications = $user->notifications()
            ->where('data->type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $transformed = $notifications->getCollection()->map(function ($notification) {
            return $this->transformNotification($notification);
        });

        $notifications->setCollection($transformed);

        return $notifications;
    }

    private function transformNotification($notification)
    {
        $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);

        return [
            'id' => $notification->id,
            'type' => $data['type'] ?? 'unknown',
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'is_read' => ! is_null($notification->read_at),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
