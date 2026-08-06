<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'receipt_number' => $data['receipt_number'],
            'invoice_number' => $data['invoice_number'],
            'type' => $data['type'],
            'date' => $data['date'],
            'payment_date' => $data['payment_date'],
            'status' => $data['status'],
            'status_description' => $data['status_description'],

            'customer' => $data['customer'],
            'delivery_address' => $data['delivery_address'],
            'payment' => $data['payment'],
            'items' => $data['items'],
            'pricing' => $data['pricing'],
            'delivery' => $data['delivery'],
            'notes' => $data['notes'],
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at'],
        ];
    }
}