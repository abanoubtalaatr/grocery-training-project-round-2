<?php

namespace App\Http\Controllers\Api\Contact;

use App\Actions\Api\Contact\GetContactStatisticsAction;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class GetContactStatisticsController extends Controller
{
    use ApiTrait;

    public function __invoke(GetContactStatisticsAction $action): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $stats = $action->run();

        return $this->dataResponse($stats);
    }
}
