<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Action\Notification\ListNotificationsAction;
use App\Action\Notification\MarkAsReadAction;
use App\Action\Notification\UpdateSettingsAction;
use App\Http\Requests\Api\NotificationSettingsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(Request $request, ListNotificationsAction $action): JsonResponse
    {
        $user = $request->user();
        $perPage = (int) $request->input('per_page', 15);
        $paginator = $action->handle($user, $perPage);

        $items = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
            ? $paginator->getCollection()->map(function ($n) { return ['id' => $n->id, 'type' => $n->type, 'data' => $n->data, 'read_at' => $n->read_at, 'created_at' => $n->created_at]; })
            : collect([]);

        return response()->json(['success' => true, 'message' => 'Notifications retrieved', 'data' => $items, 'pagination' => $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator ? ['current_page' => $paginator->currentPage(), 'last_page' => $paginator->lastPage(), 'per_page' => $paginator->perPage(), 'total' => $paginator->total()] : null]);
    }

    public function markRead(Request $request, MarkAsReadAction $action, string $id): JsonResponse
    {
        $user = $request->user();
        $ok = $action->handle($user, $id);
        return response()->json(['success' => $ok, 'message' => $ok ? 'Marked as read' : 'Notification not found']);
    }

    public function updateSettings(NotificationSettingsRequest $request, UpdateSettingsAction $action): JsonResponse
    {
        $user = $request->user();
        $res = $action->handle($user, $request->validated());
        return response()->json($res);
    }
}
