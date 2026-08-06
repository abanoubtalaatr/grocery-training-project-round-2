<?php

namespace App\Action\Api;

class GetRecentNotificationsAction
{
    public function execute($user, int $limit = 10)
    {
        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();

        $transformed = $recentNotifications->map(function ($notification) {
            return $this->transformNotification($notification);
        });

        return [
            'notifications' => $transformed,
            'total_recent' => $recentNotifications->count(),
            'unread_recent' => $recentNotifications->whereNull('read_at')->count(),
        ];
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
