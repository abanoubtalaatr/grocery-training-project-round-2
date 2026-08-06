<?php

namespace App\Actions\Api\Contact;

use App\Models\ContactMessage;

class ShowContactMessageAction
{
    public function run(ContactMessage $contactMessage): ContactMessage
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $contactMessage;
    }
}
