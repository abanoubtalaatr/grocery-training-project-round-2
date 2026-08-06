<?php

namespace App\Action\Api;

use App\Models\Setting;

class GetSettingsAction
{
    public function execute()
    {
        return Setting::getSettings();
    }
}
