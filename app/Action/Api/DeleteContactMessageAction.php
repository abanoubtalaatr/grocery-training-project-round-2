<?php

namespace App\Action\Api;

use App\Models\ContactMessage;

class DeleteContactMessageAction
{
    public function execute(ContactMessage $contactMessage): void
    {
        $contactMessage->delete();
    }
}
