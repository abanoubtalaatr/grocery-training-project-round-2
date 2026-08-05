<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Order;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $orderId;

    public function __construct(int $orderId)
    {
        $this->orderId = $orderId;
    }

    public function handle(): void
    {
        Log::info('SendEmailJob started for order ' . $this->orderId);

        $order = Order::with(['user'])->find($this->orderId);

        if (! $order) {
            Log::warning('Order not found for SendEmailJob: ' . $this->orderId);
            return;
        }

        if (empty($order->user->email)) {
            Log::warning('No email for order user: ' . $this->orderId);
            return;
        }

        if (class_exists(\App\Mail\OrderCreatedMail::class)) {
            try {
                Mail::to($order->user->email)->queue(new \App\Mail\OrderCreatedMail($order));
                Log::info('Order confirmation mail queued for order ' . $this->orderId);
            } catch (\Exception $e) {
                Log::error('Failed to queue OrderCreatedMail: ' . $e->getMessage());
                throw $e;
            }

            return;
        }

        Log::info('Would send order confirmation email to ' . $order->user->email . ' for order ' . $this->orderId);
    }
}
