<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\CreateOrderAction;
use App\Action\Api\GetOrderTrackingAction;
use App\Action\Api\GetUserOrdersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Http\Resources\Api\OrderTrackingResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetUserOrdersAction $action): JsonResponse
    {
        $orders = $action->execute($request->user());

        return $this->success(OrderResource::collection($orders),'Orders retrieved successfully');
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $order->load(['items.meal', 'address']);

        return $this->success(new OrderResource($order),'Order retrieved successfully');
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        $order = $action->execute($request->user(), $request->validated());
        $order->load(['items.meal', 'address']);

        return $this->success(new OrderResource($order),'Order created successfully',201);
    }

    public function track(Request $request, GetOrderTrackingAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return $this->success(new OrderTrackingResource($result),$result['message']);
    }
}
