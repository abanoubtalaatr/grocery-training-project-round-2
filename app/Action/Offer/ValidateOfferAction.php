<?php

namespace App\Action\Offer;

use App\Models\Offer;

class ValidateOfferAction
{
    public function handle(string $code, ?float $amount = null): array
    {
        $offer = Offer::where('code', $code)->first();

        if (! $offer) {
            return [
                'valid' => false,
                'message' => 'Invalid offer code',
                'offer' => null,
                'discount_amount' => 0,
            ];
        }

        $isValid = $offer->isValid();
        $canApply = true;
        $message = 'Offer is valid';

        if ($isValid && $amount !== null) {
            $canApply = $offer->canApplyToAmount($amount);
            if (! $canApply) {
                $message = 'Minimum purchase required: ' . ($offer->minimum_purchase ?? 0);
            }
        }

        $discount = ($canApply && $isValid) ? $offer->calculateDiscount($amount ?? 0) : 0;

        return [
            'valid' => $isValid && $canApply,
            'offer' => $offer,
            'discount_amount' => $discount,
            'message' => $message,
        ];
    }
}
