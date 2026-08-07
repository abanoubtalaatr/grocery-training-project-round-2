<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitContactMessageRequest;
use App\Http\Requests\UpdateContactStatusRequest;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    /**
     * Submit a contact message.
     */
    public function submit(SubmitContactMessageRequest $request): JsonResponse
    {
        $contactMessage = $this->contactService->submitMessage(
            data: $request->validated(),
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        if (!$contactMessage) {
            return response()->json([
                'message' => 'Your message appears to be spam',
            ], 400);
        }

        return response()->json([
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => new ContactMessageResource($contactMessage),
        ], 201);
    }

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request): ContactMessageCollection
    {
        $this->authorize('viewAny', ContactMessage::class);

        $messages = $this->contactService->getFilteredMessages($request->all());

        return new ContactMessageCollection($messages);
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage): ContactMessageResource
    {
        $this->authorize('view', $contactMessage);

        $message = $this->contactService->showMessage($contactMessage);

        return new ContactMessageResource($message);
    }

    /**
     * Update contact message status (admin only).
     */
    public function updateStatus(UpdateContactStatusRequest $request, ContactMessage $contactMessage): JsonResponse
    {
        $updatedMessage = $this->contactService->updateStatus($contactMessage, $request->validated());

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new ContactMessageResource($updatedMessage),
        ]);
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $this->contactService->deleteMessage($contactMessage);

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    /**
     * Get contact statistics (admin only).
     */
    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $stats = $this->contactService->getStatistics();

        return response()->json([
            'data' => $stats,
        ]);
    }
}