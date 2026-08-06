<?php

namespace App\Actions\Api\Stripe;

use App\Services\StripeWebhookService;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;
use InvalidArgumentException;

class HandleStripeWebhookAction
{
    public function __construct(
        private readonly StripeWebhookService $webhookService
    ) {}

    /**
     * Run the webhook handler.
     *
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws SignatureVerificationException
     * @throws \Throwable
     */
    public function run(string $payload, ?string $sigHeader): void
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            throw new InvalidArgumentException('Webhook not configured.', 500);
        }

        $event = Webhook::constructEvent(
            $payload,
            $sigHeader ?? '',
            $secret
        );

        $this->webhookService->handleEvent($event);
    }
}
