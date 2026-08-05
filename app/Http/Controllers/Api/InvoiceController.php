<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Jobs\GenerateAndSendInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function store(StoreInvoiceRequest $request)
    {
        $invoice = Auth::user()->invoices()->create([
            ...$request->validated(),
            'invoice_number' => 'INV-'.strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        GenerateAndSendInvoice::dispatch($invoice);

        return response()->json([
            'success' => true,
            'message' => 'Invoice is being generated and will be mailed shortly',
            'data' => new InvoiceResource($invoice),
        ], 202);
    }
}
