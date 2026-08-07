<?php

namespace App\Action\Admin\Notification;

use App\Models\User;
use App\Notifications\AdminNotification;

class SendNotificationToAllAction
{
    public function execute(array $data): array
    {
        $users = User::all();

        $notificationData = [
            'type' => 'admin_broadcast',
            'title' => $data['title'],
            'body' => $data['body'],
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? 'View',
            'priority' => $data['priority'] ?? 'normal',
        ];

        foreach ($users as $user) {
            $user->notify(new AdminNotification($notificationData));
        }

        return [
            'sent_to' => $users->count(),
            'title' => $data['title'],
            'body' => $data['body'],
        ];
    }
}
