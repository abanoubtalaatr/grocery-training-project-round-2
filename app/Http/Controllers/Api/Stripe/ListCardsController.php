<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Actions\Api\Stripe\ListCardsAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListCardsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, ListCardsAction $action): JsonResponse
    {
        $cards = $action->run($request->user());

        return $this->dataResponse($cards);
    }
}
