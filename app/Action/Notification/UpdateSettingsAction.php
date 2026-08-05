<?php

namespace App\Action\Notification;

class UpdateSettingsAction
{
    public function handle($user, array $data)
    {
        // Store settings on user model if attribute exists
        if (array_key_exists('notification_settings', $user->getAttributes())) {
            $user->update(['notification_settings' => $data]);
            return ['success' => true, 'message' => 'Notification settings updated'];
        }

        // Fallback: store in json column or meta table if available
        if (method_exists($user, 'setMeta')) {
            $user->setMeta('notification_settings', $data);
            return ['success' => true, 'message' => 'Notification settings updated'];
        }

        return ['success' => false, 'message' => 'Unable to persist notification settings'];
    }
}
