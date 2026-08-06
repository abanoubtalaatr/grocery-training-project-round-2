<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Contact\DestroyContactMessageAction;
use App\Actions\Api\Contact\GetContactMessagesAction;
use App\Actions\Api\Contact\ShowContactMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiTrait;

    public function index(Request $request, GetContactMessagesAction $action)
    {
        $this->authorize('viewAny', ContactMessage::class);

        $messages = $action->run($request);

        return new ContactMessageCollection($messages);
    }

    public function show(ContactMessage $contactMessage, ShowContactMessageAction $action)
    {
        $this->authorize('view', $contactMessage);

        $contactMessage = $action->run($contactMessage);

        return new ContactMessageResource($contactMessage);
    }

    public function destroy(ContactMessage $contactMessage, DestroyContactMessageAction $action): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $action->run($contactMessage);

        return $this->successResponse('Message deleted successfully');
    }
}
