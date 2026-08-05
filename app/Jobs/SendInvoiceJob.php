<?php

namespace App\Jobs;

use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Services\InvoicePdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Retry job 3 times.
     */
    public int $tries = 3;

    /**
     * Wait 30 seconds before retry.
     */
    public int $backoff = 30;

    public function __construct(
        public Order $order
    ) {}

    public function handle(InvoicePdfService $invoicePdfService): void
    {
          \Log::info('SendInvoiceJob Started');
        /**
         * Reload relationships
         */
        $this->order->load([
            'user',
            'address',
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        /**
         * Generate PDF
         */
        $pdf = $invoicePdfService->generate($this->order);

        /**
         * Send Email
         */
        Mail::to($this->order->user->email)
            ->send(
                new InvoiceMail(
                    order: $this->order,
                    pdf: $pdf
                )
            );
 
    }
}