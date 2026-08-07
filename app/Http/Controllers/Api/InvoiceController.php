<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreInvoiceRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Jobs\GenerateAndSendInvoice;
use App\Models\Invoice;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    use ApiResponse;

    /**
     * Get all invoices for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $invoices = $request->user()
            ->invoices()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->paginated($invoices, 'Invoices retrieved successfully');
    }

    /**
     * Get single invoice
     */
    public function show(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        return $this->success(
            new InvoiceResource($invoice),
            'Invoice retrieved successfully'
        );
    }

    /**
     * Create new invoice
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $request->user()->invoices()->create([
            ...$request->validated(),
            'invoice_number' => 'INV-'.strtoupper(Str::random(10)),
            'status' => 'pending',
        ]);

        GenerateAndSendInvoice::dispatch($invoice);

        return $this->success(
            new InvoiceResource($invoice),
            'Invoice is being generated and will be mailed shortly',
            202
        );
    }

    /**
     * Update invoice
     */
    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'status' => ['sometimes', 'string', 'in:pending,sent,viewed,paid'],
        ]);

        $invoice->update($validated);

        return $this->success(
            new InvoiceResource($invoice),
            'Invoice updated successfully'
        );
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return $this->success(null, 'Invoice deleted successfully');
    }
}

