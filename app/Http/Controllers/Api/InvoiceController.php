<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendInvoiceJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Send Invoice PDF via Email Queue
     */
    public function sendInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'        => ['required', 'email'],
            'order_id'     => ['required', 'integer'],
            'user_name'    => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric'],
            'items'        => ['required', 'array', 'min:1'],
        ]);

        // Dispatch job to queue
        SendInvoiceJob::dispatch($validated['email'], $validated);

        return response()->json([
            'status'  => true,
            'message' => 'Invoice request received and will be sent to email shortly.',
        ], 202);
    }
}