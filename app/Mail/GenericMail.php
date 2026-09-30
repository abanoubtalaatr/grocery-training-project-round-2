<?php

namespace App\Mail;

use App\DTOs\EmailData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailData $emailData
    ) {}

    public function build()
    {
        return $this
            ->subject($this->emailData->subject)
            ->view($this->emailData->view)
            ->with($this->emailData->data);
    }
}