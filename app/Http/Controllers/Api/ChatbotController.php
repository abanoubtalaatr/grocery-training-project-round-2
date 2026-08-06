<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\ChatbotChatAction;
use App\Action\Api\ChatbotHistoryAction;
use App\Action\Api\ChatbotSuggestionsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ChatbotChatRequest;
use App\Http\Resources\Api\ChatbotMessageResource;
use App\Http\Resources\Api\ChatbotSuggestionResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function chat(ChatbotChatRequest $request, ChatbotChatAction $action): JsonResponse
    {
        $message = $action->execute($request->user(), $request->validated());

        return $this->success(new ChatbotMessageResource($message),'Chat response generated successfully');
    }

    public function history(Request $request, ChatbotHistoryAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), (int) $request->input('per_page', 15));

        return $this->success($result,'Chat history retrieved successfully');
    }

    public function suggestions(Request $request, ChatbotSuggestionsAction $action): JsonResponse
    {
        $suggestions = $action->execute($request->input('locale', 'en'));

        return $this->success(
            ChatbotSuggestionResource::collection($suggestions),
            'Suggestions retrieved successfully'
        );
    }
}
