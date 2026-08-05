<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendPDFInvoiceJob;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PDFInvoiceController extends Controller
{
    public function sendInvoice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'exists:users,id'],
            'invoice_number' => ['required', 'string'],
            'total' => ['required', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'items' => ['nullable', 'array'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $invoiceData = [
                'invoice_number' => $request->invoice_number,
                'total' => $request->total,
                'date' => $request->date ?? now()->format('Y-m-d'),
                'items' => $request->items ?? [],
            ];

            SendPDFInvoiceJob::dispatch($request->user_id, $invoiceData);

            return response()->json([
                'success' => true,
                'message' => 'سيتم إرسال الفاتورة إلى بريدك الإلكتروني خلال لحظات',
                'data' => [
                    'user_id' => $request->user_id,
                    'invoice_number' => $request->invoice_number,
                    'queue_status' => 'pending',
                ]
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء محاولة إرسال الفاتورة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}