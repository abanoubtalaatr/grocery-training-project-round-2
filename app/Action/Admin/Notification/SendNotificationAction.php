<?php

namespace App\Action\Admin\Notification;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Validation\ValidationException;

class SendNotificationAction
{
    public function execute(array $data): array
    {
        $userIds = $data['user_ids'] ?? [];
        
        if (empty($userIds)) {
            throw ValidationException::withMessages([
                'user_ids' => ['At least one user must be specified'],
            ]);
        }

        $users = User::whereIn('id', $userIds)->get();

        if ($users->isEmpty()) {
            throw ValidationException::withMessages([
                'user_ids' => ['No valid users found'],
            ]);
        }

        $notificationData = [
            'type' => 'admin',
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
