<?php

namespace App\Jobs;

use App\Mail\InvoiceGenerated;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class GenerateAndSendInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $this->invoice]);
        $path = "invoices/{$this->invoice->invoice_number}.pdf";

        Storage::disk('local')->put($path, $pdf->output());

        $this->invoice->update([
            'pdf_path' => $path,
            'status' => 'generated',
        ]);

        Mail::to($this->invoice->user->email)
            ->send(new InvoiceGenerated($this->invoice, $path));

        $this->invoice->update(['status' => 'sent']);
    }

    public function failed(\Throwable $exception): void
    {
        $this->invoice->update(['status' => 'failed']);

        Log::error('Invoice generation failed', [
            'invoice_id' => $this->invoice->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
