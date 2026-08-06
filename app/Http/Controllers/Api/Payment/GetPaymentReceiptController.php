<?php

namespace App\Http\Controllers\Api\Payment;

use App\Actions\Api\Payment\GetPaymentReceiptAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PaymentReceiptResource;
use App\Models\Order;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class GetPaymentReceiptController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, Order $order, GetPaymentReceiptAction $action): JsonResponse
    {
        try {
            $receiptOrder = $action->run($request->user(), $order);

            return $this->dataResponse(new PaymentReceiptResource($receiptOrder), 'Receipt retrieved successfully');
        } catch (RuntimeException $e) {
            return $this->errorResponse([], $e->getMessage(), $e->getCode() ?: 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to retrieve receipt', 500);
        }
    }
}
