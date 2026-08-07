<?php

namespace App\Actions\Api\Contact;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StoreContactAction
{
    public function execute(array $validated): ContactMessage
    {
        $contactMessage = ContactMessage::create($validated);

        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($validated['email'])
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
        }

        return $contactMessage;
    }
}
