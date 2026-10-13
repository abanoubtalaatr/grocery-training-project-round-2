<?php

namespace App\Notifications;

use App\Services\PushNotificationService;
use Illuminate\Notifications\Notification;

class FcmPushChannel extends Notification
{
    public function send(object $notifiable, Notification $notification): void
    {
        $settings = $notifiable->notificationSettings;

        if (! $settings || ! $settings->push_notifications) {
            return;
        }

        if ($notifiable->pushTokens()->count() === 0) {
            return;
        }

        $data = method_exists($notification, 'toArray')
            ? $notification->toArray($notifiable)
            : [];

        $title = $data['title'] ?? 'Notification';
        $body = $data['body'] ?? '';

        $pushData = array_filter([
            'type' => $data['type'] ?? null,
            'action_url' => $data['action_url'] ?? null,
            'order_id' => isset($data['order_id']) ? (string) $data['order_id'] : null,
        ]);

        try {
            app(PushNotificationService::class)->sendToUser(
                $notifiable,
                $title,
                $body,
                $pushData
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM push channel failed: ' . $e->getMessage());
        }
    }
}
