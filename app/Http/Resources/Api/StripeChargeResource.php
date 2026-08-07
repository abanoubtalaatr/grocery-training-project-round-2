<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StripeChargeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paymentIntent = $this->resource['payment_intent'];

        return [
            'status' => $this->resource['status'],
            'payment_intent' => [
                'id' => $paymentIntent->id,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
                'status' => $paymentIntent->status,
            ],
        ];
    }
}