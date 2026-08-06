<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Cart\ClearCartAction;
use App\Actions\Api\Cart\GetCartAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CartResource;
use App\Models\Cart;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiTrait;
    /**
     * Display the authenticated user's cart.
     */
    public function index(Request $request, GetCartAction $action): JsonResponse {

        $cart = $action->run($request);

        return $this->dataResponse(new CartResource($cart), 'Cart retrieved successfully');
    }

    /**
     * Clear the authenticated user's cart.
     */
    public function destroy(Request $request, Cart $cart, ClearCartAction $action): JsonResponse {

        $cart = $action->run($cart);

        return $this->dataResponse(new CartResource($cart), 'Cart cleared successfully');
    }
}
