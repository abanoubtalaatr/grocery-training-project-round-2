<?php

namespace App\Action\Api;

use App\Services\StripeWebhookService;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class HandleStripeWebhookAction
{
    public function __construct(
        private readonly StripeWebhookService $webhookService
    ) {}

    public function execute(string $payload, string $sigHeader, string $secret): array
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $secret
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return [
                'message' => 'Invalid payload or signature.',
                'status' => 400,
            ];
        }

        try {
            $this->webhookService->handleEvent($event);
        } catch (\Throwable $e) {
            report($e);

            return [
                'message' => 'Handler error.',
                'status' => 500,
            ];
        }

        return [
            'message' => 'OK',
            'status' => 200,
        ];
    }
}