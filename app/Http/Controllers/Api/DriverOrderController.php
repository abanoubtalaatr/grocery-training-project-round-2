<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendOrderMessageRequest;
use App\Http\Requests\UpdateDeliveryStatusRequest;
use App\Http\Requests\UpdateDriverLocationRequest;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverOrderController extends Controller
{
    /**
     * Get orders assigned to the authenticated delivery partner.
     */
    public function index(Request $request): JsonResponse
    {
        $driver = $request->user();

        $query = Order::where('driver_id', $driver->id)
            ->with(['address', 'items.meal', 'user:id,username,firstname,lastname,phone']);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->orderByDesc('created_at')
            ->paginate((int) $request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Driver orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    /**
     * View details of an assigned delivery order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ($order->driver_id !== $driver->id && ! $driver->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not assigned to this delivery.',
            ], 403);
        }

        $order->load(['address', 'items.meal', 'user:id,username,firstname,lastname,phone']);

        return response()->json([
            'success' => true,
            'message' => 'Order details retrieved successfully',
            'data' => $order,
        ]);
    }

    /**
     * Update order milestone delivery status.
     */
    public function updateStatus(UpdateDeliveryStatusRequest $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ($order->driver_id !== $driver->id && ! $driver->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not assigned to this delivery.',
            ], 403);
        }

        $status = $request->validated('status');
        $updateData = ['status' => $status];

        if ($status === 'shipping' && ! $order->shipping_at) {
            $updateData['shipping_at'] = now();
        } elseif ($status === 'out_for_delivery' && ! $order->out_for_delivery_at) {
            $updateData['out_for_delivery_at'] = now();
        } elseif ($status === 'delivered' && ! $order->delivered_at) {
            $updateData['delivered_at'] = now();
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Delivery status updated successfully',
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'shipping_at' => $order->shipping_at,
                'out_for_delivery_at' => $order->out_for_delivery_at,
                'delivered_at' => $order->delivered_at,
            ],
        ]);
    }

    /**
     * Transmit driver live GPS coordinates for active deliveries.
     */
    public function updateLocation(UpdateDriverLocationRequest $request): JsonResponse
    {
        $driver = $request->user();
        $lat = $request->validated('latitude');
        $lng = $request->validated('longitude');
        $orderId = $request->validated('order_id');

        $query = Order::where('driver_id', $driver->id)
            ->whereIn('status', ['shipping', 'out_for_delivery']);

        if ($orderId) {
            $query->where('id', $orderId);
        }

        $affected = $query->update([
            'driver_latitude' => $lat,
            'driver_longitude' => $lng,
            'driver_location_updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Driver location updated successfully',
            'data' => [
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'updated_at' => now(),
                'orders_updated' => $affected,
            ],
        ]);
    }

    /**
     * List messages for an order (driver view).
     */
    public function messages(Request $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ($order->driver_id !== $driver->id && ! $driver->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not assigned to this delivery.',
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

        // Mark unread messages from the customer as read
        $order->messages()
            ->where('sender_id', '!=', $driver->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Messages retrieved successfully',
            'data' => $messages->items(),
            'unread_count' => 0,
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
                'last_page' => $messages->lastPage(),
            ],
        ]);
    }

    /**
     * Send a message to the customer (driver view).
     */
    public function sendMessage(SendOrderMessageRequest $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ($order->driver_id !== $driver->id && ! $driver->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not assigned to this delivery.',
            ], 403);
        }

        $message = $order->messages()->create([
            'sender_id' => $driver->id,
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
     * Get unread message count for the driver on a specific order.
     */
    public function unreadCount(Request $request, Order $order): JsonResponse
    {
        $driver = $request->user();

        if ($order->driver_id !== $driver->id && ! $driver->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You are not assigned to this delivery.',
            ], 403);
        }

        $count = $order->messages()
            ->where('sender_id', '!=', $driver->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'data' => ['unread_count' => $count],
        ]);
    }
}
