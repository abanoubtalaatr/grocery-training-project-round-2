<?php

namespace App\Action\Api;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class SubmitContactAction
{
    public function execute(array $data, string $ipAddress, string $userAgent): ContactMessage
    {
        if ($this->isSpam($data['message'], $data['email'])) {
            throw ValidationException::withMessages([
                'message' => ['Your message appears to be spam'],
            ]);
        }

        $contactMessage = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($data['email'])
                ->send(new ContactAutoReply($contactMessage));


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