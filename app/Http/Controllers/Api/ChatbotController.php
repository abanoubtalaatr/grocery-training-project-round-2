<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Chatbot\HandleChatAction;
use App\Http\Requests\Api\ChatRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function chat(ChatRequest $request, HandleChatAction $action): JsonResponse
    {
        $user = $request->user();
        $payload = $action->handle($user, $request->input('message'));

        return response()->json($payload);
    }
}
