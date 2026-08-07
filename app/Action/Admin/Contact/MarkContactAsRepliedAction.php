<?php

namespace App\Action\Admin\Contact;

use App\Models\ContactMessage;

class MarkContactAsRepliedAction
{
    public function execute(ContactMessage $contact): void
    {
        $contact->update(['status' => 'replied']);
    }
}
