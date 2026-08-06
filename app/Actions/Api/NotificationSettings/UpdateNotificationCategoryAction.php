<?php

namespace App\Actions\Api\NotificationSettings;

use App\Models\User;
use App\Models\UserNotificationSetting;
use InvalidArgumentException;

class UpdateNotificationCategoryAction
{
    public function run(User $user, string $category, bool $enabled): UserNotificationSetting
    {
        $settings = $user->initializeNotificationSettings();
        $fields = $this->getCategoryFields($category);

        if (empty($fields)) {
            throw new InvalidArgumentException('Invalid category');
        }

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = $enabled;
        }

        $settings->update($updateData);

        return $settings->fresh();
    }

    private function getCategoryFields(string $category): array
    {
        $categories = [
            'order_delivery' => ['order_confirmation', 'order_shipped', 'delivery_updates', 'out_of_stock_alerts'],
            'deals_promotions' => ['weekly_discounts', 'exclusive_member_offers', 'seasonal_campaigns'],
            'account_reminders' => ['cart_reminders', 'payment_billing'],
            'channels' => ['email_notifications', 'push_notifications', 'sms_notifications'],
        ];

        return $categories[$category] ?? [];
    }
}
