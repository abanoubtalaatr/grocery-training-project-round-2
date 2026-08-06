<?php

namespace App\Actions\Api\Offer;

use App\Models\Offer;

class ValidateOfferAction
{
    public function run(string $code, ?float $amount): array
    {
        $offer = Offer::where('code', $code)->first();

        if (! $offer) {
            return [
                'valid' => false,
                'offer' => null,
                'discount_amount' => 0,
                'message' => 'Invalid offer code',
            ];
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

        $discount = $canApply && $isValid
            ? $offer->calculateDiscount($amount ?? 0)
            : 0;

        return [
            'valid' => $isValid && $canApply,
            'offer' => $offer,
            'discount_amount' => $discount,
            'message' => $message,
        ];
    }
}
