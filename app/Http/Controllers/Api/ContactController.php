<?php

namespace App\Http\Controllers\Api;

use App\Action\Api\SubmitContactAction;
use App\Action\Api\UpdateContactStatusAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SubmitContactRequest;
use App\Http\Requests\Api\UpdateContactStatusRequest;
use App\Http\Resources\Api\ContactMessageResource;
use App\Http\Resources\Api\ContactStatisticResource;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function submit(SubmitContactRequest $request, SubmitContactAction $action): JsonResponse
    {
        $contactMessage = $action->execute($request->validated(), $request->ip(), $request->userAgent());

        return $this->success(new ContactMessageResource($contactMessage),'Thank you for your message. We will get back to you soon.',201);
    }

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

        return $this->success(
            ContactMessageResource::collection($messages),
            'Contact messages retrieved successfully'
        );
    }

    public function show(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('view', $contactMessage);

        if ($contactMessage->status === 'new') {
            $contactMessage->markAsRead();
        }

        return $this->success(new ContactMessageResource($contactMessage->fresh()),'Contact message retrieved successfully');
    }

    public function updateStatus(UpdateContactStatusRequest $request, ContactMessage $contactMessage, UpdateContactStatusAction $action): JsonResponse
    {
        $this->authorize('update', $contactMessage);

        $action->execute($contactMessage, $request->validated());

        return $this->success(new ContactMessageResource($contactMessage),'Status updated successfully');
    }

    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $this->authorize('delete', $contactMessage);

        $contactMessage->delete();

        return $this->success(null, 'Message deleted successfully');
    }

    public function statistics(): JsonResponse
    {
        $this->authorize('viewAny', ContactMessage::class);

        $total = ContactMessage::count();
        $new = ContactMessage::new()->count();
        $read = ContactMessage::read()->count();
        $replied = ContactMessage::replied()->count();
        $spam = ContactMessage::spam()->count();

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

        return $this->success(
            [
                'total' => $total,
                'new' => $new,
                'read' => $read,
                'replied' => $replied,
                'spam' => $spam,
                'monthly_stats' => $monthlyStats,
            ],
            'Statistics retrieved successfully'
        );
    }
}
