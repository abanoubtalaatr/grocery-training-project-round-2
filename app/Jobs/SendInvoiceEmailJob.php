<?php

namespace App\Jobs;
use App\Models\Order;
use App\Mail\InvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

public function handle()
{
    $this->order->load([
        'user',
        'items.meal'
    ]);

    Mail::to($this->order->user->email) //minamaherwanis@gamil.com
        ->send(new InvoiceMail($this->order));
}
}