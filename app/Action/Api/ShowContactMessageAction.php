<?php

namespace App\Action\Api;

use App\Models\ContactMessage;

class ShowContactMessageAction
{
    public function execute(ContactMessage $contactMessage): ContactMessage
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $contactMessage;
    }
}
