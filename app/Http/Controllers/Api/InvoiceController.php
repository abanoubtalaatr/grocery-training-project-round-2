<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Jobs\SendInvoiceEmailJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Send invoice to the authenticated user's email.
     */
    public function sendInvoice(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        // Ensure the order belongs to the authenticated user
        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        SendInvoiceEmailJob::dispatch($order);

        return response()->json([
            'success' => true,
            'message' => 'Invoice email has been queued and will be sent shortly',
        ]);
    }
}