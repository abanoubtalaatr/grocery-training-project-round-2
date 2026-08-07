<?php

namespace App\Actions\Api\Contacts;

use App\Mail\ContactAutoReply;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class SubmitContactMessageAction
{
    public function execute(array $data, Request $request): ContactMessage
    {
        if ($this->isSpam($data['message'])) {
            throw new InvalidArgumentException('Your message appears to be spam');
        }

        $contactMessage = ContactMessage::create([
            ...$data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            Mail::to(config('mail.admin_email', 'admin@example.com'))
                ->send(new ContactMessageReceived($contactMessage));

            Mail::to($data['email'])
                ->send(new ContactAutoReply($contactMessage));
        } catch (\Exception $exception) {
            Log::error('Failed to send contact email: ' . $exception->getMessage());
        }

        return $contactMessage;
    }

    private function isSpam(string $message): bool
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
