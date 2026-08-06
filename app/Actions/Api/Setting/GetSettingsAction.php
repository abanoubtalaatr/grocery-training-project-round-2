<?php

namespace App\Actions\Api\Setting;

use App\Models\Setting;

class GetSettingsAction
{
    public function run(): Setting
    {
        return Setting::getSettings();
    }
}
