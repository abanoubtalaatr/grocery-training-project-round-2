<?php

namespace App\Http\Controllers\Api\Contact;

use App\Actions\Api\Contact\UpdateContactStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateContactStatusRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class UpdateContactStatusController extends Controller
{
    use ApiTrait;

    public function __invoke(
        UpdateContactStatusRequest $request,
        ContactMessage $contactMessage,
        UpdateContactStatusAction $action
    ): JsonResponse {
        $this->authorize('update', $contactMessage);

        $contactMessage = $action->run($contactMessage, $request->validated());

        return $this->dataResponse(
            new ContactMessageResource($contactMessage),
            'Status updated successfully'
        );
    }
}
