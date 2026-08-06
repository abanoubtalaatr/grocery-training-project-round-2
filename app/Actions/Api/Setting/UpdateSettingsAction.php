<?php

namespace App\Actions\Api\Setting;

use App\Models\Setting;

class UpdateSettingsAction
{
    public function run(array $data): Setting
    {
        $settings = Setting::getSettings();

        // Handle file uploads if they are set in the data array
        if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            $data['logo'] = $data['logo']->store('settings', 'public');
        }

        if (isset($data['favicon']) && $data['favicon'] instanceof \Illuminate\Http\UploadedFile) {
            $data['favicon'] = $data['favicon']->store('settings', 'public');
        }

        $settings->update($data);

        return $settings;
    }
}
