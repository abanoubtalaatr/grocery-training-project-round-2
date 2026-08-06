<?php

namespace App\Actions\Api\Chatbot;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class GetChatHistoryAction
{
    public function run(User $user, int $perPage = 15): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        return $user->chatbotMessages()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
