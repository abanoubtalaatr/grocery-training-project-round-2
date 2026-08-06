<?php

namespace App\Action\Api;

class GetRecentNotificationsAction
{
    public function execute($user): array
    {
        $recentNotifications = $user->notifications()
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return [
            'notifications' => $recentNotifications,
            'total_recent' => $recentNotifications->count(),
            'unread_recent' => $recentNotifications->whereNull('read_at')->count(),
        ];
    }
}