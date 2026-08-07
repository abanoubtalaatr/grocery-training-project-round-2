<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function handle(): void
    {
        $this->order->load([
            'user',
            'items.meal',
        ]);

        if (! $this->order->user?->email) {
            return;
        }

        Mail::to($this->order->user->email)
            ->send(new InvoiceMail($this->order));
    }
}