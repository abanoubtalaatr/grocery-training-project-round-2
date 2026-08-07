<?php

namespace App\Http\Controllers\Api;

use App\Actions\Api\Contacts\SubmitContactMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContactMessageRequest;
use App\Http\Requests\Api\UpdateContactStatusRequest;
use App\Http\Resources\ContactMessageCollection;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContactController extends Controller
{
    public function submit(StoreContactMessageRequest $request, SubmitContactMessageAction $submitContactMessage): JsonResponse
    {
        try {
            $contactMessage = $submitContactMessage->execute($request->validated(), $request);
        } catch (InvalidArgumentException $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->successResponse(
            new ContactMessageResource($contactMessage),
            'Thank you for your message. We will get back to you soon.',
            201
        );
    }

    public function index(Request $request): ContactMessageCollection
    {
        $this->authorize('viewAny', ContactMessage::class);

        $query = ContactMessage::query()
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('to_date')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('subject', 'LIKE', "%{$search}%")
                        ->orWhere('message', 'LIKE', "%{$search}%");
                });
            });

        $query->orderBy($request->input('sort_by', 'created_at'), $request->input('sort_order', 'desc'));

        return new ContactMessageCollection($query->paginate($request->integer('per_page', 20)));
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('view', $contactMessage);

        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $this->successResponse(new ContactMessageResource($contactMessage), 'Contact message retrieved successfully');
    }

    public function updateStatus(UpdateContactStatusRequest $request, ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('update', $contactMessage);

        $contactMessage->update($request->validated());

        return $this->successResponse(new ContactMessageResource($contactMessage), 'Status updated successfully');
    }

    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return $this->successResponse(null, 'Message deleted successfully');
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $monthlyStats = ContactMessage::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m") as month,
            COUNT(*) as total,
            SUM(CASE WHEN status = "new" THEN 1 ELSE 0 END) as new,
            SUM(CASE WHEN status = "replied" THEN 1 ELSE 0 END) as replied
        ')
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return $this->successResponse([
            'total' => ContactMessage::count(),
            'new' => ContactMessage::new()->count(),
            'read' => ContactMessage::read()->count(),
            'replied' => ContactMessage::replied()->count(),
            'spam' => ContactMessage::spam()->count(),
            'monthly_stats' => $monthlyStats,
        ]);
    }
}
