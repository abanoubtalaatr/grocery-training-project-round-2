<?php
namespace App\Services;


use App\Models\Order;
use App\Notifications\InvoiceReadyNotification;
use App\Services\ReceiptService;
use Barryvdh\DomPDF\Facade\Pdf;
// use App\Models\Invoice;

use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(
    private ReceiptService $receiptService
) {}
      public function generate(Order $order): void
    {
        $order->load([
            'user',
            'items.meal',
            'address',
        ]);

        $pdfContent = $this->buildPdf($order);

        $path = $this->storePdf($pdfContent, $order);

        $this->sendNotification($order, $path);
    }
    //     private function buildPdf(Order $order): string
    // {
    //     return Pdf::loadView('pdf.invoice', [
    //         'order' => $order,
    //     ])->output();
    // }
private function buildPdf(Order $order): string
{
    $receipt = $this->receiptService->format($order);

    return Pdf::loadView('pdf.invoice', [
        'receipt' => $receipt,
    ])->output();
}
    private function storePdf(string $pdfContent, Order $order): string
    {
        $path = "invoices/invoice-{$order->id}.pdf";

        Storage::disk('public')->put($path, $pdfContent);

        return $path;
    }

    private function sendNotification(Order $order, string $path): void
    {
        $downloadUrl = Storage::disk('public')->url($path);

        $order->user->notify(
            new InvoiceReadyNotification($downloadUrl)
        );
    }


}