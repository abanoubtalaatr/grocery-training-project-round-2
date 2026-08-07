<?php

namespace App\Http\Controllers\Api;

use App\Models\SpecialNote;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecialNoteResource;
use Illuminate\Http\JsonResponse;

class SpecialNoteController extends Controller
{
    public function index(): JsonResponse
    {
        $specialNotes = SpecialNote::all();

        return $this->successResponse(
            SpecialNoteResource::collection($specialNotes),
            'Special notes retrieved successfully'
        );
    }
}
