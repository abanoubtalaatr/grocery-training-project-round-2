<?php
namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class InvoiceService
{
    public function makePdf(Order $order): DomPdfWrapper
    {
        // Load relationships needed for the PDF template
        $order->load(['user', 'items.meal', 'address']);

        return Pdf::loadView('pdf.invoice', compact('order'))
            ->setPaper('a4', 'portrait');
    }

    public function generatePdfBinary(Order $order): string
    {
        return $this->makePdf($order)->output();
    }
}