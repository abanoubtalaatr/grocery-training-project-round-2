<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Stripe\HandleStripeWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;
use InvalidArgumentException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, HandleStripeWebhookAction $action): Response
    {
        try {
            $action->run(
                $request->getContent(),
                $request->header('Stripe-Signature')
            );
        } catch (InvalidArgumentException $e) {
            return response($e->getMessage(), $e->getCode() ?: 500);
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid payload or signature.', 400);
        } catch (\Throwable $e) {
            report($e);
            return response('Handler error.', 500);
        }

        return response('OK', 200);
    }
}
