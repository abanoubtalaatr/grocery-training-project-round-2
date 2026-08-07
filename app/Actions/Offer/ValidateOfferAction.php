<?php

namespace App\Actions\Offer;

use App\Models\Offer;

class ValidateOfferAction
{
    public function execute(array $data)
    {
        $offer = Offer::where('code', $data['code'])->first();

        if (! $offer) {
            return [
                'valid' => false,
                'offer' => null,
                'discount' => 0,
                'message' => 'Invalid offer code',
            ];
        }

        $isValid = $offer->isValid();

        $canApply = true;

        $message = 'Offer is valid';

        if (isset($data['amount'])) {
            $canApply = $offer->canApplyToAmount($data['amount']);

            if (! $canApply) {
                $message = 'Minimum purchase required: $'.$offer->minimum_purchase;
            }
        }

        return [
            'valid' => $isValid && $canApply,
            'offer' => $offer,
            'discount' => ($isValid && $canApply)
                ? $offer->calculateDiscount($data['amount'] ?? 0)
                : 0,
            'message' => $message,
        ];
    }
}