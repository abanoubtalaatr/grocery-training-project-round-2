<?php

namespace App\Action\Api;

use App\Models\Setting;

class UpdateSettingsAction
{
    public function execute(array $data)
    {
        $settings = Setting::getSettings();

        // Handle file uploads if provided as UploadedFile instances
        if (! empty($data['logo'])) {
            if (is_object($data['logo']) && method_exists($data['logo'], 'store')) {
                $data['logo'] = $data['logo']->store('settings', 'public');
            }
        }

        if (! empty($data['favicon'])) {
            if (is_object($data['favicon']) && method_exists($data['favicon'], 'store')) {
                $data['favicon'] = $data['favicon']->store('settings', 'public');
            }
        }

        $settings->update($data);

        return $settings->fresh();
    }
}
