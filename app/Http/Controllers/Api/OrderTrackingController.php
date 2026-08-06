<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Order\TrackOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OrderTrackingResource;
use App\Traits\ApiTrait;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, TrackOrderAction $action)
    {
        $order = $action->run($request);

        if (!$order) {
            return $this->errorResponse([], 'No active order found', 404);
        }

        if ($order->status === 'awaiting_payment') {
            return $this->dataResponse(
                [
                    'order' => new OrderTrackingResource($order),
                    'awaiting_payment' => true,
                    'tracking' => null,
                ],
                'Order is waiting for payment. Complete checkout to continue.'
            );
        }

        return $this->dataResponse(
            new OrderTrackingResource($order),
            'Order tracking retrieved successfully'
        );
    }
}