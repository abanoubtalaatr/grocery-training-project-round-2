<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Stripe\HandleStripeWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;
use InvalidArgumentException;
use App\Traits\ApiTrait;
class StripeWebhookController extends Controller
{
    use ApiTrait;
    public function __invoke(Request $request, HandleStripeWebhookAction $action): Response
    {

            $action->run(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );


        return $this->successResponse('Webhook received successfully.');
    }
}
