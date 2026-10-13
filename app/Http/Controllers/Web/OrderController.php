<?php

namespace App\Http\Controllers\Web;

use App\Actions\Api\CancelOrderAction;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = $user->orders()
            ->with(['items.meal', 'address', 'notes.specialNote'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $activeOrdersCount = $user->orders()->active()->count();

        return view('orders.index', [
            'orders' => $orders,
            'activeOrdersCount' => $activeOrdersCount,
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load(['items.meal.category', 'items.meal.subcategory', 'address', 'notes.specialNote']);

        $canCancel = ! in_array($order->status, ['cancelled', 'delivered', 'shipping', 'out_for_delivery']);

        return view('orders.show', [
            'order' => $order,
            'canCancel' => $canCancel,
        ]);
    }

    public function track(Request $request, Order $order): View
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            abort(404);
        }

        $order->load(['items.meal', 'address', 'driver', 'notes.specialNote']);

        $isSlaBreached = false;
        $minutesUntilBreach = null;

        if ($order->estimated_delivery_time && in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery'])) {
            $isSlaBreached = $order->estimated_delivery_time->isPast();
            if (!$isSlaBreached) {
                $minutesUntilBreach = (int) now()->diffInMinutes($order->estimated_delivery_time, false);
            }
        }

        $hasGps = $order->driver_latitude !== null && $order->driver_longitude !== null;

        $positions = [
            ['key' => 'placed', 'label' => trans('orders.detail.status_placed'), 'position' => 1, 'timestamp_field' => 'placed_at'],
            ['key' => 'processing', 'label' => trans('orders.detail.status_processing'), 'position' => 2, 'timestamp_field' => 'processing_at'],
            ['key' => 'shipping', 'label' => trans('orders.detail.status_shipping'), 'position' => 3, 'timestamp_field' => 'shipping_at'],
            ['key' => 'out_for_delivery', 'label' => trans('orders.detail.status_out_for_delivery'), 'position' => 4, 'timestamp_field' => 'out_for_delivery_at'],
            ['key' => 'delivered', 'label' => trans('orders.detail.status_delivered'), 'position' => 5, 'timestamp_field' => 'delivered_at'],
        ];

        return view('orders.track', [
            'order' => $order,
            'positions' => $positions,
            'hasGps' => $hasGps,
            'isSlaBreached' => $isSlaBreached,
            'minutesUntilBreach' => $minutesUntilBreach,
        ]);
    }

    public function trackJson(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();

        if ($order->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $order->load(['address', 'driver']);

        $hasGps = $order->driver_latitude !== null && $order->driver_longitude !== null;

        $isSlaBreached = false;
        if ($order->estimated_delivery_time && in_array($order->status, ['placed', 'processing', 'shipping', 'out_for_delivery'])) {
            $isSlaBreached = $order->estimated_delivery_time->isPast();
        }

        $statusColors = [
            'awaiting_payment' => 'bg-yellow-100 text-yellow-800',
            'placed' => 'bg-blue-100 text-blue-800',
            'processing' => 'bg-indigo-100 text-indigo-800',
            'shipping' => 'bg-purple-100 text-purple-800',
            'out_for_delivery' => 'bg-orange-100 text-orange-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $order->status,
                'status_description' => $order->status_description,
                'status_color' => $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800',
                'position' => $order->status_position,
                'driver' => $order->driver ? [
                    'name' => $order->driver->full_name ?? $order->driver->username,
                    'phone' => $order->driver->phone,
                    'avatar' => $order->driver->profile_image_url,
                ] : null,
                'has_gps' => $hasGps,
                'driver_location_updated_at' => $order->driver_location_updated_at?->toISOString(),
                'estimated_delivery_time' => $order->estimated_delivery_time?->toISOString(),
                'is_sla_breached' => $isSlaBreached,
                'delivered_at' => $order->delivered_at?->toISOString(),
                'cancelled_at' => $order->cancelled_at?->toISOString(),
            ],
        ]);
    }

    public function cancel(Request $request, Order $order, CancelOrderAction $cancelAction): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $cancelAction->execute($user, $order, $validated['reason'] ?? null);

            return back()->with('success', trans('orders.cancel.success'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e) {
            abort(403);
        }
    }
}
