<?php

namespace App\Http\Controllers\Api\Stripe;

use App\Actions\Api\Stripe\DeleteCardAction;
use App\Http\Controllers\Controller;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class DeleteCardController extends Controller
{
    use ApiTrait;

    public function __invoke(string $id, DeleteCardAction $action): JsonResponse
    {
        $action->run($id);

        return $this->dataResponse(['status' => 'deleted'], 'Card deleted successfully');
    }
}
