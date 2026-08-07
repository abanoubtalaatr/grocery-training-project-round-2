<?php

namespace App\Action\Admin\Contact;

use App\Models\ContactMessage;

class GetContactStatsAction
{
    public function execute(): array
    {
        return [
            'total' => ContactMessage::count(),
            'new' => ContactMessage::where('status', 'new')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
            'replied' => ContactMessage::where('status', 'replied')->count(),
            'spam' => ContactMessage::where('status', 'spam')->count(),
        ];
    }
}
