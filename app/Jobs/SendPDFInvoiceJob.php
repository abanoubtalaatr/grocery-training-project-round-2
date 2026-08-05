<?php

namespace App\Jobs;

use App\Mail\PDFInvoiceMail;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SendPDFInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $invoiceData;
    public $tries = 3;
    public $backoff = 60;

    public function __construct($userId, array $invoiceData = [])
    {
        $this->userId = $userId;
        $this->invoiceData = $invoiceData;
    }

    public function handle(): void
    {
        try {
            $user = User::findOrFail($this->userId);

            $pdf = Pdf::loadView('pdfs.invoice', [
                'user' => $user,
                'invoiceData' => $this->invoiceData,
            ]);

            $pdfPath = storage_path('app/temp/invoice-' . uniqid() . '.pdf');
            
            if (!is_dir(dirname($pdfPath))) {
                mkdir(dirname($pdfPath), 0777, true);
            }
            
            $pdf->save($pdfPath);

            Mail::to($user->email)->send(new PDFInvoiceMail($user, $pdfPath, $this->invoiceData));

            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }

            \Log::info('PDF invoice sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to send PDF invoice: ' . $e->getMessage());
            throw $e;
        }
    }
}