<?php

namespace App\Actions\Admin\Support;

use App\Models\ContactMessage;

class DestroySupportAction
{
    public function run(ContactMessage $support): void
    {
        $support->delete();
    }
}
