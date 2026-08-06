<?php

namespace App\Http\Controllers\Api\Chatbot;

use App\Actions\Api\Chatbot\GetChatSuggestionsAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ChatbotSuggestionResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetChatSuggestionsController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetChatSuggestionsAction $action): JsonResponse
    {
        $suggestions = $action->run($request->input('locale', 'en'));

        return $this->dataResponse([
            'suggestions' => ChatbotSuggestionResource::collection($suggestions),
        ], 'Suggestions retrieved successfully');
    }
}
