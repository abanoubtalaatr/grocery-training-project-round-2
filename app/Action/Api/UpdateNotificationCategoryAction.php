<?php

namespace App\Action\Api;

use Illuminate\Validation\ValidationException;

class UpdateNotificationCategoryAction
{
    public function execute($user, string $category, array $data)
    {
        $fields = $this->getCategoryFields($category);

        if (empty($fields)) {
            throw ValidationException::withMessages([
                'category' => ['Invalid category'],
            ]);
        }

        $settings = $user->initializeNotificationSettings();

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = (bool) $data['enabled'];
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