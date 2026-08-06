<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\SpecialNote\GetSpecialNotesAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecialNoteResource;
use App\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;

class SpecialNoteController extends Controller
{
    use ApiTrait;

    public function index(GetSpecialNotesAction $action): JsonResponse
    {
        $specialNotes = $action->run();

        return $this->dataResponse(SpecialNoteResource::collection($specialNotes));
    }
}
