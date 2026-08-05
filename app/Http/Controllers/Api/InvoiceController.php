<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendInvoiceJob;
use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Services\InvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class InvoiceController extends Controller
{
public function send(Order $order): JsonResponse
{
    try {

        SendInvoiceJob::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Invoice queued successfully.',
        ]);

    } catch (\Throwable $e) {

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
}