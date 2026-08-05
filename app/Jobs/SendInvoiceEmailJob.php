<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $email;
    public $base64Pdf;

    public function __construct(string $email, string $base64Pdf)
    {
        $this->email = $email;
        $this->base64Pdf = $base64Pdf;
    }

    public function handle(): void
    {
        $pdfContent = base64_decode($this->base64Pdf);
        Mail::to($this->email)->send(new InvoiceMail($pdfContent));
    }
}
