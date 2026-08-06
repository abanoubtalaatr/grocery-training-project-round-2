<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Api\Auth\DeleteAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeleteAccountRequest;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class DeleteAccountController extends Controller
{
    use ApiTrait;

    public function __invoke(DeleteAccountRequest $request, DeleteAccountAction $action): JsonResponse
    {
        try {
            $action->run($request->user());

            return $this->successResponse('Account deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'Failed to delete account', 500);
        }
    }
}
