<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\HandleStripeWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StripeWebhookController extends Controller
{
    public function handle(Request $request, HandleStripeWebhookAction $action): Response
    {
        $secret = config('services.stripe.webhook_secret');

        if (!is_string($secret) || $secret === '') {
            return response('Webhook not configured.', 500);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $result = $action->execute($payload, $sigHeader ?? '', $secret);

        return response($result['message'], $result['status']);
    }
}
