<?php

namespace App\Action\Api;

use Illuminate\Notifications\DatabaseNotification;

class GetNotificationStatsAction
{
    public function execute($user): array
    {
        $allNotifications = $user->notifications();
        $unreadNotifications = $user->unreadNotifications();

        $total = $allNotifications->count();
        $unread = $unreadNotifications->count();

        $typeCounts = $allNotifications->get()
            ->groupBy(function (DatabaseNotification $n) {
                $data = $this->notificationDataAsArray($n->data);

                return $data['type'] ?? 'unknown';
            })
            ->map(function ($notifications) {
                return [
                    'total' => $notifications->count(),
                    'unread' => $notifications->whereNull('read_at')->count(),
                ];
            });

        $recentTypes = $allNotifications->latest()
            ->take(5)
            ->get()
            ->map(function (DatabaseNotification $n) {
                $data = $this->notificationDataAsArray($n->data);

                return $data['type'] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        $last = $allNotifications->latest()->first();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => max(0, $total - $unread),
            'by_type' => $typeCounts,
            'recent_types' => $recentTypes,
            'last_notification_at' => $last?->created_at?->toIso8601String(),
        ];
    }

    private function notificationDataAsArray(mixed $data): array
    {
        if (is_array($data)) {
            return $data;
        }

        if (is_string($data) && $data !== '') {
            $decoded = json_decode($data, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
