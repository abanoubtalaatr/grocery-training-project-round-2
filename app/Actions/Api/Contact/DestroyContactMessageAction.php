<?php

namespace App\Actions\Api\Contact;

use App\Models\ContactMessage;

class DestroyContactMessageAction
{
    public function run(ContactMessage $contactMessage): bool
    {
        return (bool) $contactMessage->delete();
    }
}
