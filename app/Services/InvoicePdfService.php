<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoicePdfService
{
    /**
     * Generate invoice PDF.
     */
    public function generate(Order $order)
    {
        $order->loadMissing([
            'user',
            'address',
            'items.meal.category',
            'items.meal.subcategory',
        ]);

        return Pdf::loadView('pdf.invoice', [
                'order' => $order,
            ])
            ->setPaper('a4', 'portrait');
    }
}