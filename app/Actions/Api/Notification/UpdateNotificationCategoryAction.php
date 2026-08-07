<?php

namespace App\Actions\Api\Notification;

use Illuminate\Support\Facades\Auth;

class UpdateNotificationCategoryAction
{
    public function execute(string $category, bool $enabled): array
    {
        $user = Auth::user();
        $settings = $user->initializeNotificationSettings();

        $fields = $this->getCategoryFields($category);

        if (empty($fields)) {
            return ['success' => false, 'message' => 'Invalid category'];
        }

        $updateData = [];
        foreach ($fields as $field) {
            $updateData[$field] = $enabled;
        }

        $settings->update($updateData);

        return ['success' => true, 'settings' => $settings->fresh()];
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
