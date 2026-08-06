<?php

namespace App\Action\Api;

use App\Http\Resources\Api\ChatbotMessageResource;

class ChatbotHistoryAction
{
    public function execute($user, int $perPage = 15): array
    {
        $perPage = min(max($perPage, 1), 50);

        $messages = $user->chatbotMessages()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return [
            'items' => ChatbotMessageResource::collection($messages->items()),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'from' => $messages->firstItem(),
                'to' => $messages->lastItem(),
            ],
        ];
    }
}