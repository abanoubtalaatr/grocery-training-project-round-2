<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInvoiceJob;
use App\Models\Order;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    protected InvoiceService $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
       $this->invoiceService=$invoiceService;

    }

    public function sendInvoice(Order $order){
        // Push work to queue worker
        ProcessInvoiceJob::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Invoice queued successfully. It will be emailed shortly.',
            'order_id' => $order->id,
        ], Response::HTTP_ACCEPTED);
        
    }

    public function downloadInvoice(Order $order)
    {
        $pdf = $this->invoiceService->makePdf($order);

        return $pdf->download("Invoice-{$order->order_number}.pdf");
    }
}
