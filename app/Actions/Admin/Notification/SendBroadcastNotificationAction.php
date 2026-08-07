<?php

namespace App\Actions\Admin\Notification;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Str;

class SendBroadcastNotificationAction
{
    public function run(array $data): int
    {
        $notificationData = [
            'type' => $data['type'] ?? 'admin_broadcast',

            'notifiable_type' => User::class,

            'data' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'type' => $data['type'] ?? 'admin_broadcast',
            ],
        ];

        if ($data['target'] === 'all') {
            $users = User::where('is_admin', false)->pluck('id');

            foreach ($users as $userId) {
                Notification::create([
                    'id' => (string) Str::uuid(),
                    ...$notificationData,
                    'notifiable_id' => $userId,
                ]);
            }

            return $users->count();
        }

        foreach ($data['user_ids'] as $userId) {
            Notification::create([
                'id' => (string) Str::uuid(),
                ...$notificationData,
                'notifiable_id' => $userId,
            ]);
        }

        return count($data['user_ids']);
    }
}