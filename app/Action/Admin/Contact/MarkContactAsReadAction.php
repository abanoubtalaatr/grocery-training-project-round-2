<?php

namespace App\Action\Admin\Contact;

use App\Models\ContactMessage;

class MarkContactAsReadAction
{
    public function execute(ContactMessage $contact): void
    {
        $contact->markAsRead();
    }
}
