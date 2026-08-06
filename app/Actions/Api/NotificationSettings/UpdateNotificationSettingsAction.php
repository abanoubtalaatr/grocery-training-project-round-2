<?php

namespace App\Actions\Api\NotificationSettings;

use App\Models\User;
use App\Models\UserNotificationSetting;

class UpdateNotificationSettingsAction
{
    public function run(User $user, array $validated): UserNotificationSetting
    {
        $settings = $user->initializeNotificationSettings();
        $settings->update($validated);

        return $settings->fresh();
    }
}
