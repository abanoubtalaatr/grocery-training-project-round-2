<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user->id,
            'order_id' => $this->order->id,
            'invoice_number' => $this->invoice_number,
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
