<?php

namespace App\Action\Api;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ContactMessageReceived;

class SubmitContactAction
{
    /**
     * @param  array<string,mixed>  $data
     * @return ContactMessage
     */
    public function execute(array $data): ContactMessage
    {
        // Spam detection (same logic as controller)
        if ($this->isSpam((string) ($data['message'] ?? ''), (string) ($data['email'] ?? ''))) {
            throw new \RuntimeException('spam_detected');
        }

        $contactMessage = ContactMessage::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'user_agent' => $data['user_agent'] ?? null,
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($contactMessage->email)
                ->send(new \App\Mail\ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: '.$e->getMessage());
        }

        return $contactMessage;
    }

    private function isSpam(string $message, string $email): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'loan', 'debt', 'free money',
            'work from home', 'make money fast', 'click here',
        ];

        $message = strtolower($message);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
