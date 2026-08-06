<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\CartItem\AddCartItemAction;
use App\Actions\Api\CartItem\DeleteCartItemAction;
use App\Actions\Api\CartItem\UpdateCartItemAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCartItemRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\Api\CartResource;
use App\Models\CartItem;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartItemsController extends Controller
{
    use ApiTrait;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCartItemRequest $request,AddCartItemAction $action): JsonResponse {

        $cart = $action->run($request);

        return $this->dataResponse(new CartResource($cart), 'Item added to cart successfully', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCartItemRequest $request,CartItem $cartItem,UpdateCartItemAction $action ): JsonResponse {

        $cart = $action->run($request, $cartItem);

        return $this->dataResponse(new CartResource($cart), 'Cart item updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, CartItem $cartItem,DeleteCartItemAction $action): JsonResponse {

        $cart = $action->run($cartItem);

        return $this->dataResponse(new CartResource($cart), 'Item removed from cart successfully');
    }
}