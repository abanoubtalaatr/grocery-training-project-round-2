<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendInvoiceRequest;
use App\Models\Order;
use App\Jobs\SendInvoiceEmailJob;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Send invoice to the authenticated user's email.
     */
    use ApiResponseTrait;
    public function sendInvoice(SendInvoiceRequest $request, Order $order): JsonResponse
    {
        SendInvoiceEmailJob::dispatch($order);
        return $this->successResponse('Invoice email has been queued and will be sent shortly',null);
    }
}