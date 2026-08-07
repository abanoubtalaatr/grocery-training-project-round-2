<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Contact\StoreContactAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\Api\ContactMessageResource;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    /**
     * Get all contact messages (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $query = ContactMessage::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('subject', 'LIKE', "%{$search}%")
                    ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->get('per_page', 20);
        $messages = $query->paginate($perPage);

        return $this->paginated($messages, 'Contact messages retrieved successfully');
    }

    /**
     * Show specific contact message (admin only).
     */
    public function show(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('view', $contactMessage);

        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $this->success(
            new ContactMessageResource($contactMessage),
            'Contact message retrieved successfully'
        );
    }

    /**
     * Create contact message (public submission).
     */
    public function store(StoreContactRequest $request, StoreContactAction $action): JsonResponse
    {
        $validated = array_merge(
            $request->validated(),
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        );

        $contactMessage = $action->execute($validated);

        return $this->success(
            new ContactMessageResource($contactMessage),
            'Thank you for your message. We will get back to you soon.',
            201
        );
    }

    /**
     * Update contact message status (admin only).
     */
    public function update(UpdateContactRequest $request, ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('update', $contactMessage);

        $contactMessage->update($request->validated());

        return $this->success(
            new ContactMessageResource($contactMessage),
            'Contact message updated successfully'
        );
    }

    /**
     * Delete contact message (admin only).
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return $this->success(null, 'Message deleted successfully');
    }
}
