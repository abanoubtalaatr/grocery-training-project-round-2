<?php

namespace App\Actions\Admin\Support;

use App\Models\ContactMessage;

class ShowSupportAction
{
    public function run(ContactMessage $support): ContactMessage
    {
        if ($support->status === 'new') {
            $support->markAsRead();
        }

        return $support;
    }
}
