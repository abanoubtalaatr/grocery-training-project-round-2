<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateSetupIntentAction;
use App\Action\Api\ListStripeCardsAction;
use App\Action\Api\ChargeSavedCardAction;
use App\Action\Api\DeleteCardAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    use \App\Traits\ApiResponse;

    public function createSetupIntent(Request $request, CreateSetupIntentAction $action)
    {
        $intent = $action->execute($request->user());

        return $this->success(['clientSecret' => $intent->client_secret], 'Setup intent created');
    }

    public function listCards(Request $request, ListStripeCardsAction $action)
    {
        $cards = $action->execute($request->user());

        return $this->success($cards, 'Cards retrieved successfully');
    }

    public function chargeSavedCard(Request $request, ChargeSavedCardAction $action)
    {
        $data = $request->validate([
            'payment_method_id' => 'required|string',
            'amount' => 'required|numeric',
        ]);

        $paymentIntent = $action->execute($request->user(), $data['payment_method_id'], (float) $data['amount']);

        return $this->success(['payment_intent' => $paymentIntent], 'Charge successful');
    }

    public function deleteCard(Request $request, $id, DeleteCardAction $action)
    {
        $action->execute($id);

        return $this->success(null, 'Card deleted');
    }
}
