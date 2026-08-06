<?php

namespace App\Action\Api;

class UpdateNotificationSettingsAction
{
    public function execute($user, array $validated)
    {
        $settings = $user->initializeNotificationSettings();
        $settings->update($validated);

        return $settings->fresh();
    }

    public function updateCategory($user, string $category, bool $enabled)
    {
        $settings = $user->initializeNotificationSettings();

        $fields = $this->getCategoryFields($category);

        if (empty($fields)) {
            return null;
        }

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = (bool) $enabled;
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
