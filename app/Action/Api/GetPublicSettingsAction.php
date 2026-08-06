<?php

namespace App\Action\Api;

use App\Models\Setting;

class GetPublicSettingsAction
{
    public function execute()
    {
        return Setting::getSettings();
    }
}