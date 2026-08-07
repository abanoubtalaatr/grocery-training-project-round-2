<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Stripe\ChargeSavedCardAction;
use App\Actions\Api\Stripe\CreateStripeSetupIntentAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentMethod;
use Stripe\Stripe;

class StripeController extends Controller
{
    use ApiResponse;

    /**
     * Create Stripe setup intent for card registration
     */
    public function createSetupIntent(Request $request, CreateStripeSetupIntentAction $action): JsonResponse
    {
        $user = $request->user();
        $data = $action->execute($user);

        return $this->success($data, 'Setup intent created successfully');
    }

    /**
     * Get all saved payment methods (cards) for the user
     */
    public function listCards(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $user = $request->user();

        if (! $user->stripe_customer_id) {
            return $this->success([], 'No cards found');
        }

        $cards = PaymentMethod::all([
            'customer' => $user->stripe_customer_id,
            'type' => 'card',
        ]);

        return $this->success($cards->data, 'Cards retrieved successfully');
    }

    /**
     * Charge a saved payment method
     */
    public function chargeSavedCard(Request $request, ChargeSavedCardAction $action): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $user = $request->user();
        $data = $action->execute($user, $validated['payment_method_id'], $validated['amount']);

        return $this->success($data, 'Payment processed successfully');
    }

    /**
     * Delete a saved payment method
     */
    public function deleteCard(Request $request, string $id): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $paymentMethod = PaymentMethod::retrieve($id);
        $paymentMethod->detach();

        return $this->success(['status' => 'deleted'], 'Card deleted successfully');
    }
}
