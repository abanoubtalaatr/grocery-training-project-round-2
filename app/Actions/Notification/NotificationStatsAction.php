<?php

namespace App\Actions\Notification;

use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationStatsAction
{
    public function execute()
    {
        $user = Auth::user();

        $notifications = $user->notifications();

        $total = $notifications->count();
        $unread = $user->unreadNotifications()->count();

        $typeCounts = $notifications->get()
            ->groupBy(function (DatabaseNotification $notification) {
                return data_get($notification->data, 'type', 'unknown');
            })
            ->map(function ($items) {
                return [
                    'total' => $items->count(),
                    'unread' => $items->whereNull('read_at')->count(),
                ];
            });

        $recentTypes = $notifications
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($n) => data_get($n->data, 'type'))
            ->filter()
            ->unique()
            ->values();

        $last = $notifications->latest()->first();

        return [
            'total' => $total,
            'unread' => $unread,
            'read' => $total - $unread,
            'by_type' => $typeCounts,
            'recent_types' => $recentTypes,
            'last_notification_at' => $last?->created_at?->toIso8601String(),
        ];
    }
}