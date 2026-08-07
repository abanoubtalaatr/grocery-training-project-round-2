<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'user' => $this->when($this->relationLoaded('user'), [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),
            'status' => $this->status,
            'status_description' => $this->status_description,
            'payment_method' => $this->payment_method,
            'delivery_type' => $this->delivery_type,
            'items' => $this->when($this->relationLoaded('items'), 
                $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'meal' => $item->meal ? [
                        'id' => $item->meal->id,
                        'title' => $item->meal->title,
                        'image_url' => $item->meal->image_url,
                    ] : null,
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'subtotal' => (float) $item->subtotal,
                ])
            ),
            'address' => $this->when($this->relationLoaded('address') && $this->address, [
                'id' => $this->address->id,
                'full_name' => $this->address->full_name,
                'phone' => $this->address->phone,
                'full_address' => $this->address->full_address,
                'city' => $this->address->city,
            ]),
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'discount' => (float) $this->discount,
            'shipping_fee' => (float) ($this->shipping_fee ?? 0),
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'admin_notes' => $this->admin_notes,
            'placed_at' => $this->placed_at,
            'processing_at' => $this->processing_at,
            'shipping_at' => $this->shipping_at,
            'out_for_delivery_at' => $this->out_for_delivery_at,
            'delivered_at' => $this->delivered_at,
            'cancelled_at' => $this->cancelled_at,
            'estimated_delivery_time' => $this->estimated_delivery_time,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
