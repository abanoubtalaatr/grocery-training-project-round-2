<?php

namespace App\Http\Controllers\Api\Chatbot;

use App\Actions\Api\Chatbot\SendChatMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendChatMessageRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use RuntimeException;
use Throwable;

class SendChatMessageController extends Controller
{
    use ApiTrait;

    public function __invoke(SendChatMessageRequest $request, SendChatMessageAction $action): JsonResponse
    {
        $data = $action->run(
            $request->user(),
            $request->validated('question'),
            $request->validated('rating'),
            $request->validated('locale')
            );

            return $this->dataResponse($data, 'Chat response generated successfully');

    }
}
