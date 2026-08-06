<?php

namespace App\Actions\Api\Contact;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubmitContactAction
{
    public function run(array $data, Request $request): ContactMessage
    {
        if ($this->isSpam($data['message'], $data['email'])) {
            throw new \LogicException('Your message appears to be spam');
        }

        $contactMessage = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($data['email'])
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $e) {
            Log::error('Failed to send contact email: ' . $e->getMessage());
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
