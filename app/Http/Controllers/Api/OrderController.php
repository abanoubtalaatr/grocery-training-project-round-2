<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Order\IndexOrderAction;
use App\Actions\Api\Order\ShowOrderAction;
use App\Actions\Api\Order\StoreOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Order;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiTrait;

    public function index(Request $request, IndexOrderAction $action)
    {
        return $this->dataResponse(
            OrderResource::collection($action->run($request)),
            'Orders retrieved successfully'
        );
    }

    public function store(StoreOrderRequest $request, StoreOrderAction $action)
    {
        return $this->dataResponse(
            new OrderResource($action->run($request)),
            'Order created successfully',
            201
        );
    }

    public function show(Request $request, Order $order, ShowOrderAction $action)
    {
        return $this->dataResponse(
            new OrderResource($action->run($request, $order)),
            'Order retrieved successfully'
        );
    }
}