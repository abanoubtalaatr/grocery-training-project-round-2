<?php

namespace App\Http\Controllers\Admin;

use App\Action\Admin\Order\ExportOrdersAction;
use App\Action\Admin\Order\GetOrderStatsAction;
use App\Action\Admin\Order\GetOrdersAction;
use App\Action\Admin\Order\UpdateOrderStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\Admin\OrderResource;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request, GetOrdersAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success(
            [
                'orders' => OrderResource::collection($result['orders']),
                'pagination' => $result['pagination'],
            ],
            'Orders retrieved successfully'
        );
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.meal.category', 'address', 'user:id,username,firstname,lastname,email,phone']);

        return $this->success(new OrderResource($order),'Order retrieved successfully');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, UpdateOrderStatusAction $action): JsonResponse
    {
        $action->execute($order, $request->status, $request->notes ?? null);

        return $this->success(new OrderResource($order->fresh()->load(['items.meal.category', 'address', 'user'])),'Order status updated successfully');
    }

    public function stats(GetOrderStatsAction $action): JsonResponse
    {
        $stats = $action->execute();

        return $this->success($stats, 'Order statistics retrieved successfully');
    }

    public function export(Request $request, ExportOrdersAction $action): JsonResponse
    {
        $result = $action->execute($request->all());

        return $this->success($result, 'Orders exported successfully');
    }
}
