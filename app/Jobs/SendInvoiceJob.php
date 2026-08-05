<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /**
     * Create a new job instance.
     */
    public function __construct(protected int $id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with([
        'user',
        'items.meal',
        'address',
    ])->findOrFail($this->id);

        $pdf = Pdf::loadView(
            'invoices.invoice',
            [
                'order' => $order
            ]
        );

        Mail::to($order->user->email)
            ->send(new InvoiceMail(
                $order,
                $pdf->output()
            ));
    }
}
