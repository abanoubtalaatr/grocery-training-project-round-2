<?php

namespace App\Action\Admin\Setting;

use App\Models\Setting;

class GetSettingsAction
{
    public function execute()
    {
        return Setting::getSettings();
    }
}
