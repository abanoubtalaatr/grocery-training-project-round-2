<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\InvoiceMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class SendInvoiceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->loadMissing([
            'items.meal.category',
            'items.meal.subcategory',
            'address',
            'user',
        ]);

        $receipt = $this->formatReceipt($this->order);

        $pdf = Pdf::loadView('pdf.invoice', ['receipt' => $receipt]);

        $relativePath = 'invoices/' . $this->order->order_number . '.pdf';
        Storage::disk('local')->put($relativePath, $pdf->output());
        $fullPath = Storage::disk('local')->path($relativePath);

        Mail::to($this->order->user->email)
            ->send(new InvoiceMail($this->order, $fullPath));

        Storage::disk('local')->delete($relativePath);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Invoice email failed for order ' . $this->order->id, [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Build the receipt data array from the order.
     * (Same structure as PaymentController::formatReceipt)
     */
    private function formatReceipt(Order $order): array
    {
        $user = $order->user;
        $address = $order->address;

        return [
            'receipt_number' => $order->order_number,
            'invoice_number' => 'INV-' . str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'date' => $order->placed_at ?? $order->created_at,
            'status' => $order->status,

            'customer' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->username ?? 'Customer',
                'email' => $user->email,
                'phone' => $user->phone,
            ],

            'delivery_address' => $address ? [
                'full_address' => $address->full_address,
                'street_address' => $address->street_address,
            ] : null,

            'items' => $order->items->map(function ($item) {
                return [
                    'meal' => [
                        'title' => $item->meal->title,
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ];
            }),

            'pricing' => [
                'subtotal' => (float) $order->subtotal,
                'tax' => (float) $order->tax,
                'discount' => (float) $order->discount,
                'total' => (float) $order->total,
            ],
        ];
    }
}