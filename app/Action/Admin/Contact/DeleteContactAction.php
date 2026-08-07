<?php

namespace App\Action\Admin\Contact;

use App\Models\ContactMessage;

class DeleteContactAction
{
    public function execute(ContactMessage $contact): void
    {
        $contact->delete();
    }
}
