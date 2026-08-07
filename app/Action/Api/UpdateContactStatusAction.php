<?php

namespace App\Action\Api;

use App\Models\ContactMessage;

class UpdateContactStatusAction
{
    public function execute(ContactMessage $contactMessage, array $data): void
    {
        $contactMessage->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? null,
        ]);
    }
}