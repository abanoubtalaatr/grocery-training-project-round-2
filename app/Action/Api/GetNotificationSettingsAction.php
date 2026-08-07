<?php

namespace App\Action\Api;

use App\Models\UserNotificationSetting;

class GetNotificationSettingsAction
{
    public function execute($user)
    {
        return $user->initializeNotificationSettings() ?: $this->defaultSettings();
    }

    private function defaultSettings(): UserNotificationSetting
    {
        return new UserNotificationSetting([
            'order_confirmation' => true,
            'order_shipped' => true,
            'delivery_updates' => true,
            'out_of_stock_alerts' => true,
            'weekly_discounts' => true,
            'exclusive_member_offers' => true,
            'seasonal_campaigns' => true,
            'cart_reminders' => true,
            'payment_billing' => true,
            'email_notifications' => true,
            'push_notifications' => true,
            'sms_notifications' => false,
        ]);
    }
}