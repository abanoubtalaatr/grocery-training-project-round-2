<?php

namespace App\Action\Api;

class UpdateNotificationSettingsAction
{
    public function execute($user, array $data)
    {
        $settings = $user->initializeNotificationSettings();
        $settings->update($data);

        return $settings->fresh();
    }
}