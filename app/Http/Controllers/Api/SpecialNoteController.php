<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecialNoteResource;
use App\Models\SpecialNote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpecialNoteController extends Controller
{
    use ApiResponse;

    /**
     * Get all special notes
     */
    public function index(): JsonResponse
    {
        $specialNotes = SpecialNote::all();

        return $this->success(
            SpecialNoteResource::collection($specialNotes),
            'Special notes retrieved successfully'
        );
    }

    /**
     * Get single special note
     */
    public function show(SpecialNote $specialNote): JsonResponse
    {
        return $this->success(
            new SpecialNoteResource($specialNote),
            'Special note retrieved successfully'
        );
    }

    /**
     * Create new special note
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'string', 'in:warning,info,notice'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $specialNote = SpecialNote::create($validated);

        return $this->success(
            new SpecialNoteResource($specialNote),
            'Special note created successfully',
            201
        );
    }

    /**
     * Update special note
     */
    public function update(Request $request, SpecialNote $specialNote): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['sometimes', 'string', 'max:1000'],
            'type' => ['sometimes', 'string', 'in:warning,info,notice'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $specialNote->update($validated);

        return $this->success(
            new SpecialNoteResource($specialNote),
            'Special note updated successfully'
        );
    }

    /**
     * Delete special note
     */
    public function destroy(SpecialNote $specialNote): JsonResponse
    {
        $specialNote->delete();

        return $this->success(null, 'Special note deleted successfully');
    }
}

