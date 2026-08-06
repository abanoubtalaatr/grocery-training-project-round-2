<?php

namespace App\Actions\Api\Contact;

use App\Models\ContactMessage;

class UpdateContactStatusAction
{
    public function run(ContactMessage $contactMessage, array $data): ContactMessage
    {
        $contactMessage->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);

        return $contactMessage;
    }
}
