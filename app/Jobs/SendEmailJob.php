<?php

namespace App\Jobs;

use App\DTOs\EmailData;
use App\Mail\GenericMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public EmailData $emailData
    ) {}

    public function handle(): void
    {
        $mail = new GenericMail($this->emailData);

        foreach ($this->emailData->attachments as $attachment) {
            $mail->attach($attachment);
        }

        Mail::to($this->emailData->to)
            ->send($mail);
    }
}