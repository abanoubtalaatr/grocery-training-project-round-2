<?php

namespace App\Jobs;

use App\Mail\SendInvoiceMail;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ProcessInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    protected Order $order;
    /**
     * Create a new job instance.
     */
   public function __construct( Order $order) 
   {
    $this->order=$order;
   }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoiceService): void
    {
        $pdfContent = $invoiceService->generatePdfBinary($this->order);
        Mail::to($this->order->user->email)
            ->send(new SendInvoiceMail($this->order, $pdfContent));
    }
}
