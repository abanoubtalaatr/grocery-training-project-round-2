<?php

namespace App\Http\Controllers\Api;

use App\Actions\Cart\ClearCartAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClearCartController extends Controller
{
    use ApiResponse;

    /**
     * Clear cart
     */
    public function __invoke(Request $request, ClearCartAction $action): JsonResponse
    {
        $cart = $request->user()->getOrCreateCart();
        
        $this->authorize('destroy', $cart);

        $clearedCart = $action->execute($request->user());

        return $this->Success(new CartResource($clearedCart), 'Cart cleared successfully');
    }
}
