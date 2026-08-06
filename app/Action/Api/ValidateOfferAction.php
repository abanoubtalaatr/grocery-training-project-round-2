<?php

namespace App\Action\Api;

use App\Models\Offer;

class ValidateOfferAction
{
    public function execute(string $code, ?float $amount = null): array
    {
        $offer = Offer::where('code', $code)->first();

        if (! $offer) {
            return ['valid' => false, 'message' => 'Invalid offer code', 'offer' => null, 'discount' => 0];
        }

        $isValid = $offer->isValid();
        $canApply = true;
        $message = 'Offer is valid';

        if ($isValid && $amount !== null) {
            $canApply = $offer->canApplyToAmount($amount);
            if (! $canApply) {
                $message = 'Minimum purchase required: $' . $offer->minimum_purchase;
            }
        }

        $discount = $canApply && $isValid ? $offer->calculateDiscount($amount ?? 0) : 0;

        return ['valid' => $isValid && $canApply, 'message' => $message, 'offer' => $offer, 'discount' => $discount];
    }
}
