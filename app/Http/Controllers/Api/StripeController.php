<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Stripe\CreateSetupIntentAction;
use App\Action\Stripe\ListCardsAction;
use App\Action\Stripe\ChargeSavedCardAction;
use App\Action\Stripe\DeleteCardAction;
use App\Http\Requests\Api\ChargeSavedCardRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StripeController extends Controller
{
    public function createSetupIntent(Request $request, CreateSetupIntentAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user);

        return response()->json($result);
    }

    public function listCards(Request $request, ListCardsAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user);

        return response()->json($result['data'] ?? []);
    }

    public function chargeSavedCard(ChargeSavedCardRequest $request, ChargeSavedCardAction $action): JsonResponse
    {
        $user = $request->user();

        $result = $action->handle($user, $request->input('payment_method_id'), (float) $request->input('amount'));

        if (! $result['status']) {
            return response()->json(['success' => false, 'message' => $result['message'] ?? 'Charge failed'], 422);
        }

        return response()->json(['success' => true, 'payment_intent' => $result['payment_intent']]);
    }

    public function deleteCard(Request $request, string $id, DeleteCardAction $action): JsonResponse
    {
        $action->handle($id);

        return response()->json(['status' => 'deleted']);
    }
}
