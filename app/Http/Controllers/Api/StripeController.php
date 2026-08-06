<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ChargeSavedCardAction;
use App\Action\Api\CreateSetupIntentAction;
use App\Action\Api\DeleteStripeCardAction;
use App\Action\Api\ListStripeCardsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChargeSavedCardRequest;
use App\Http\Resources\Api\StripeCardResource;
use App\Http\Resources\Api\StripeChargeResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    use ApiResponse;

    public function createSetupIntent(Request $request, CreateSetupIntentAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return $this->success(['client_secret' => $result['client_secret']],'Setup intent created successfully');
    }

    public function listCards(Request $request, ListStripeCardsAction $action): JsonResponse
    {
        $cards = $action->execute($request->user());

        return $this->success(StripeCardResource::collection($cards),'Cards retrieved successfully');
    }

    public function chargeSavedCard(ChargeSavedCardRequest $request, ChargeSavedCardAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            $request->payment_method_id,
            $request->amount
        );

        return $this->success(new StripeChargeResource($result),'Payment processed successfully');
    }

    public function deleteCard(Request $request, string $id, DeleteStripeCardAction $action): JsonResponse
    {
        $action->execute($id);

        return $this->success(null, 'Card deleted successfully');
    }
}
