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
        try {
            $data = $action->run(
                $request->user(),
                $request->validated('question'),
                $request->validated('rating'),
                $request->validated('locale')
            );

            return $this->dataResponse($data, 'Chat response generated successfully');
        } catch (RuntimeException $e) {
            return $this->errorResponse(
                $e->getMessage(),
                'Failed to get response from AI',
                $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 502
            );
        } catch (Throwable $e) {
            return $this->errorResponse(
                config('app.debug') ? $e->getMessage() : 'Internal server error',
                'Failed to process chat request',
                500
            );
        }
    }
}
