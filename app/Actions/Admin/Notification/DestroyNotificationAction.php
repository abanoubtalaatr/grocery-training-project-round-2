<?php

namespace App\Actions\Admin\Notification;

use App\Models\Notification;

class DestroyNotificationAction
{
    public function run(string $id): void
    {
        Notification::destroy($id);
    }
}
