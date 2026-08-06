<?php

namespace App\Http\Controllers\Api\Contact;

use App\Actions\Api\Contact\SubmitContactAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitContactRequest;
use App\Http\Resources\ContactMessageResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use LogicException;

class SubmitContactController extends Controller
{
    use ApiTrait;

    public function __invoke(SubmitContactRequest $request, SubmitContactAction $action): JsonResponse
    {
        try {
            $contactMessage = $action->run($request->validated(), $request);

            return $this->dataResponse(
                new ContactMessageResource($contactMessage),
                'Thank you for your message. We will get back to you soon.',
                201
            );
        } catch (LogicException $e) {
            return $this->errorResponse([], $e->getMessage(), 400);
        }
    }
}
