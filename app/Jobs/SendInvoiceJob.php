<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $userEmail,
        public array $invoiceData
    ) {}

    public function handle(): void
    {
        try {
            // تنفيذ عملية إرسال الإيميل
            Mail::send([], [], function ($message) {
                $message->to($this->userEmail)
                        ->subject('Invoice #' . $this->invoiceData['order_id'])
                        ->html('<p>Hello ' . $this->invoiceData['user_name'] . ', please find your invoice attached.</p>');
            });

            Log::info("Invoice sent successfully to: {$this->userEmail}");
        } catch (\Throwable $e) {
            Log::error("Failed to send invoice to {$this->userEmail}: " . $e->getMessage());
            throw $e;
        }
    }
}