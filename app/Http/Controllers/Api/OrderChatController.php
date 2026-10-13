<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOrderMessageRequest;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderChatController extends Controller
{
    /**
     * List messages for an order (customer view).
     */
    public function index(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $perPage = min(max((int) $request->get('per_page', 50), 1), 100);
        $afterId = $request->input('after_id');

        $query = $order->messages()
            ->with('sender:id,username,firstname,lastname,role')
            ->orderBy('created_at', 'asc');

        if ($afterId) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->paginate($perPage);

        // Mark unread messages from the driver as read
        $order->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $unreadCount = 0;

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $messages->items(),
            'unread_count' => $unreadCount,
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    /**
     * Send a message to the driver (customer view).
     */
    public function store(SendOrderMessageRequest $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        if (! $order->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'No driver assigned to this order yet.',
            ], 422);
        }

        $message = $order->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        $message->load('sender:id,username,firstname,lastname,role');

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    /**
     * Get unread message count for the customer.
     */
    public function unreadCount(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $count = $order->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
        ]);
    }
}
