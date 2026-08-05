<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Contact\SubmitContactAction;
use App\Http\Requests\Api\ContactRequest;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function submit(ContactRequest $request, SubmitContactAction $action): JsonResponse
    {
        $result = $action->handle($request->validated());
        return response()->json($result);
    }
}
