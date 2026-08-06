<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\EmailValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Submit a contact message.
     */
    public function submit(\App\Http\Requests\Api\ContactSubmitRequest $request, \App\Action\Api\SubmitContactAction $action)
    {
        $data = $request->validated();
        $data['ip_address'] = $request->ip();
        $data['user_agent'] = $request->userAgent();

        try {
            $contactMessage = $action->execute($data);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'spam_detected') {
                return response()->json(['message' => 'Your message appears to be spam'], 400);
            }

            throw $e;
        }

        return response()->json([
            'message' => 'Thank you for your message. We will get back to you soon.',
            'data' => new ContactMessageResource($contactMessage),
        ], 201);
    }

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request, \App\Action\Api\ListContactMessagesAction $action)
    {
        $this->authorize('viewAny', ContactMessage::class);

        $filters = [
            'status' => $request->get('status'),
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'search' => $request->get('search'),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_order' => $request->get('sort_order', 'desc'),
        ];

        $perPage = (int) $request->get('per_page', 20);
        $messages = $action->execute($filters, $perPage);

        return new ContactMessageCollection($messages);
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage, \App\Action\Api\ShowContactMessageAction $action)
    {
        $this->authorize('view', $contactMessage);

        $contactMessage = $action->execute($contactMessage);

        return new ContactMessageResource($contactMessage);
    }

    /**
     * Update contact message status (admin only).
     */
    public function updateStatus(\App\Http\Requests\Api\ContactUpdateStatusRequest $request, ContactMessage $contactMessage, \App\Action\Api\UpdateContactStatusAction $action)
    {
        $this->authorize('update', $contactMessage);

        $data = $request->validated();

        $contactMessage = $action->execute($contactMessage, $data);

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => new ContactMessageResource($contactMessage),
        ]);
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage, \App\Action\Api\DeleteContactMessageAction $action)
    {
        $this->authorize('delete', $contactMessage);

        $action->execute($contactMessage);

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    /**
     * Get contact statistics (admin only).
     */
    public function statistics(\App\Action\Api\ContactStatisticsAction $action)
    {
        $this->authorize('viewAny', ContactMessage::class);

        $data = $action->execute();

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Simple spam detection.
     */
    private function isSpam($message, $email): bool
    {
        $spamKeywords = [
            'viagra', 'casino', 'loan', 'debt', 'free money',
            'work from home', 'make money fast', 'click here',
        ];

        $message = strtolower($message);

        foreach ($spamKeywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
