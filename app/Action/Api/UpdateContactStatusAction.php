<?php

namespace App\Action\Api;

use App\Models\ContactMessage;

class UpdateContactStatusAction
{
    public function execute(ContactMessage $contactMessage, array $data): ContactMessage
    {
        $contactMessage->update([
            'status' => $data['status'] ?? $contactMessage->status,
            'admin_notes' => $data['admin_notes'] ?? $contactMessage->admin_notes,
        ]);

        return $contactMessage;
    }
}
