<?php

namespace App\Action\Contact;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubmitContactAction
{
    public function handle(array $data)
    {
        // Try to use Contact model if it exists
        try {
            if (class_exists(\App\Models\Contact::class)) {
                $model = \App\Models\Contact::create($data);
            }
        } catch (\Throwable $e) {
            Log::warning('Contact model not available or create failed', ['error' => $e->getMessage()]);
            $model = null;
        }

        // Send email to site admin if mail is configured
        try {
            $to = config('mail.from.address');
            if ($to) {
                Mail::raw($data['message'] ?? '', function ($m) use ($data, $to) {
                    $m->to($to)->subject($data['subject'] ?? 'Contact message');
                });
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send contact email', ['error' => $e->getMessage()]);
        }

        return [
            'success' => true,
            'message' => 'Contact submitted successfully',
            'data' => $model ? $model : $data,
        ];
    }
}
