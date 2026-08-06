<?php

namespace App\Http\Controllers\Api\Chatbot;

use App\Actions\Api\Chatbot\GetChatHistoryAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ChatbotMessageResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GetChatHistoryController extends Controller
{
    use ApiTrait;

    public function __invoke(Request $request, GetChatHistoryAction $action): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $messages = $action->run($request->user(), $perPage);

            return $this->dataResponse([
                'items' => ChatbotMessageResource::collection($messages->getCollection()),
                'pagination' => [
                    'current_page' => $messages->currentPage(),
                    'last_page' => $messages->lastPage(),
                    'per_page' => $messages->perPage(),
                    'total' => $messages->total(),
                    'from' => $messages->firstItem(),
                    'to' => $messages->lastItem(),
                ],
            ], 'Chat history retrieved successfully');
        } catch (Throwable $e) {
            return $this->errorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                'Failed to retrieve chat history',
                500
            );
        }
    }
}
