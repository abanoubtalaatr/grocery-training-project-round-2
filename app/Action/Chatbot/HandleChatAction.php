<?php

namespace App\Action\Chatbot;

use Psr\Log\LoggerInterface;

class HandleChatAction
{
    public function handle($user, string $message): array
    {
        // Prefer existing chatbot service if available
        try {
            if (app()->bound(\App\Services\ChatbotService::class)) {
                $service = app(\App\Services\ChatbotService::class);
                return $service->reply($user, $message);
            }
        } catch (\Throwable $e) {
            app(LoggerInterface::class)->error('Chatbot service error', ['error' => $e->getMessage()]);
        }

        // Fallback: echo back the message (safe default)
        return [
            'success' => true,
            'message' => 'Chatbot is temporarily unavailable. Here is an echo of your message.',
            'data' => ['reply' => $message],
        ];
    }
}
