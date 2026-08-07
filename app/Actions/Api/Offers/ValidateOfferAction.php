<?php

namespace App\Actions\Api\Offers;

use App\Models\Offer;

class ValidateOfferAction
{
    public function execute(array $data): array
    {
        $offer = Offer::where('code', $data['code'])->first();

        if (!$offer) {
            return [
                'valid' => false,
                'offer' => null,
                'discount_amount' => 0,
                'message' => 'Invalid offer code',
                'status' => 404,
            ];
        }

        $isValid = $offer->isValid();
        $canApply = true;
        $message = 'Offer is valid';

        if ($isValid && array_key_exists('amount', $data)) {
            $canApply = $offer->canApplyToAmount($data['amount']);

            if (!$canApply) {
                $message = 'Minimum purchase required: $' . $offer->minimum_purchase;
            }
        }

        return [
            'valid' => $isValid && $canApply,
            'offer' => $offer,
            'discount_amount' => $canApply && $isValid ? $offer->calculateDiscount($data['amount'] ?? 0) : 0,
            'message' => $message,
            'status' => 200,
        ];
    }
}
