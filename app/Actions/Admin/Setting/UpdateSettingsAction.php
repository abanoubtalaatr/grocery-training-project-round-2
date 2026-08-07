<?php

namespace App\Actions\Admin\Setting;

use App\Models\Setting;

class UpdateSettingsAction
{
    public function run(array $data): Setting
    {
        $settings = Setting::getSettings();
        $settings->update($data);

        return $settings;
    }
}
