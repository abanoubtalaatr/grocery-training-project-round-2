<?php

namespace App\Http\Controllers\Api;

use App\Actions\Profile\DeleteProfileImageAction;
use App\Actions\Profile\UpdateProfileImageAction;
use App\Actions\Profile\UpdateProfileInfoAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileImageRequest;
use App\Http\Requests\Api\UpdateProfileInfoRequest;
use App\Http\Resources\Api\AddressResource;
use App\Http\Resources\Api\NotificationResource;
use App\Http\Resources\Api\OrderResource;
use App\Http\Resources\Api\UserResource;

use App\Models\Address;
use App\Models\Order;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    private const PROFILE_SINGLE_IMAGE_MESSAGE = 'Only one profile image is allowed';

    /**
     * Get full user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['addresses', 'favorites.meal.category', 'favorites.meal.subcategory']);

        $addresses = AddressResource::collection(
            $user->addresses()->orderBy('is_default', 'desc')->orderBy('created_at', 'desc')->get()
        );

        $allOrders = Order::where('user_id', $user->id)
            ->with(['items.meal.category', 'items.meal.subcategory', 'address'])
            ->orderBy('created_at', 'desc')
            ->get();

        $orderHistory = $allOrders->map(fn (Order $o) => $this->formatOrderSummary($o));

        $inProgressOrders = $allOrders->whereNotIn('status', ['cancelled', 'delivered']);
        $inProgressWithTracking = $inProgressOrders->map(fn (Order $o) => $this->formatOrderWithTracking($o))->values();

        $orderNotifications = NotificationResource::collection(
            $user->notifications()
                ->where(function ($q) {
                    $q->where('data->type', 'order_confirmation')
                        ->orWhere('data->type', 'order_shipped')
                        ->orWhere('data->type', 'delivery_updates');
                })
                ->orderBy('created_at', 'desc')
                ->take(20)
                ->get()
        );

        $sessions = $this->formatSessions($user);
        $wishlist = $user->favorites->map(fn ($f) => $this->formatWishlistItem($f))->values();

        return $this->success([
            'me'                   => new UserResource($user),
            'addresses'            => $addresses,
            'order_history'        => [
                'orders'     => $orderHistory,
                'ordered_at' => $orderHistory->map(fn ($o) => $o['placed_at'] ?? $o['created_at'])->values(),
            ],
            'in_progress_orders'   => $inProgressWithTracking,
            'order_notifications'  => $orderNotifications,
            'settings'             => [
                'privacy_and_security' => [
                    'active_sessions' => $sessions,
                    'change_password' => ['available' => true],
                    'change_username' => ['available' => true],
                ],
            ],
            'wishlist'             => $wishlist,
        ], 'Profile retrieved successfully');
    }

    /**
     * Update profile image
     */
    public function updateImage(UpdateProfileImageRequest $request, UpdateProfileImageAction $action): JsonResponse
    {
        if (count($request->allFiles()) > 1 || is_array($request->file('image'))) {
            return $this->validationError(
                ['image' => [self::PROFILE_SINGLE_IMAGE_MESSAGE]],
                self::PROFILE_SINGLE_IMAGE_MESSAGE
            );
        }

        $user = $action->execute($request->user(), $request->file('image'));

        return $this->success([
            'profile_image'     => $user->profile_image,
            'profile_image_url' => $user->profile_image_url,
        ], 'Profile image updated successfully');
    }

    /**
     * Update profile information
     */
    public function updateInfo(UpdateProfileInfoRequest $request, UpdateProfileInfoAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), $request->validated());

        return $this->success(
            new UserResource($user),
            'Profile updated successfully'
        );
    }

    /**
     * Delete profile image
     */
    public function deleteImage(Request $request, DeleteProfileImageAction $action): JsonResponse
    {
        $action->execute($request->user());

        return $this->success(null, 'Profile image deleted successfully');
    }

    /**
     * List active sessions/devices
     */
    public function sessions(Request $request): JsonResponse
    {
        $sessions = $this->formatSessions($request->user());

        return $this->success($sessions, 'Sessions retrieved successfully');
    }

    /**
     * Revoke a session/device
     */
    public function destroySession(Request $request, string $tokenId): JsonResponse
    {
        $user = $request->user();
        $currentTokenId = $user->currentAccessToken()?->id;

        if ((string) $tokenId === (string) $currentTokenId) {
            return $this->error('Cannot revoke your current session from this request. Use logout instead.', 400);
        }

        $token = $user->tokens()->find($tokenId);
        if (! $token) {
            return $this->notFound('Session not found');
        }

        $token->delete();

        return $this->success(null, 'Session revoked successfully');
    }

    private function formatOrderSummary(Order $order): array
    {
        return [
            'id'                 => $order->id,
            'order_number'       => $order->order_number,
            'status'             => $order->status,
            'status_description' => $order->status_description,
            'total'              => (float) $order->total,
            'placed_at'          => $order->placed_at?->toIso8601String(),
            'created_at'         => $order->created_at?->toIso8601String(),
            'item_count'         => $order->items->count(),
        ];
    }

    private function formatOrderWithTracking(Order $order): array
    {
        $trackingStage = match ($order->status) {
            'shipping'         => 'arriving',
            'out_for_delivery' => 'out_for_delivery',
            'delivered'        => 'delivered',
            default            => 'processing',
        };

        return [
            'id'                     => $order->id,
            'order_number'           => $order->order_number,
            'status'                 => $order->status,
            'status_description'     => $order->status_description,
            'tracking'               => [
                'stage'       => $trackingStage,
                'stage_label' => match ($trackingStage) {
                    'arriving'         => 'Arriving',
                    'out_for_delivery' => 'Out for delivery',
                    'delivered'        => 'Delivered',
                    default            => 'Processing',
                },
                'positions'   => [
                    ['stage' => 'arriving', 'label' => 'Arriving', 'completed' => in_array($order->status, ['shipping', 'out_for_delivery', 'delivered']), 'timestamp' => $order->shipping_at?->toIso8601String()],
                    ['stage' => 'out_for_delivery', 'label' => 'Out for delivery', 'completed' => in_array($order->status, ['out_for_delivery', 'delivered']), 'timestamp' => $order->out_for_delivery_at?->toIso8601String()],
                    ['stage' => 'delivered', 'label' => 'Delivered', 'completed' => $order->status === 'delivered', 'timestamp' => $order->delivered_at?->toIso8601String()],
                ],
            ],
            'total'                  => (float) $order->total,
            'placed_at'              => $order->placed_at?->toIso8601String(),
            'estimated_delivery_time'=> $order->estimated_delivery_time?->toIso8601String(),
            'address'                => $order->address ? new AddressResource($order->address) : null,
            'items'                  => $order->items->map(fn ($item) => [
                'id'       => $item->id,
                'meal'     => ['id' => $item->meal->id, 'title' => $item->meal->title, 'image_url' => $item->meal->image_url],
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ])->values(),
        ];
    }

    private function formatSessions($user): array
    {
        $currentTokenId = $user->currentAccessToken()?->id;

        return $user->tokens()->get()->map(function ($token) use ($currentTokenId) {
            return [
                'id'           => $token->id,
                'name'         => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'is_current'   => (string) $token->id === (string) $currentTokenId,
            ];
        })->all();
    }

    private function formatWishlistItem($favorite): array
    {
        $meal = $favorite->meal;

        return [
            'id'           => $meal->id,
            'title'        => $meal->title,
            'slug'         => $meal->slug,
            'image_url'    => $meal->image_url,
            ...$meal->getApiPriceAttributes(),
            'has_offer'    => $meal->hasOffer(),
            'category'     => $meal->category ? ['id' => $meal->category->id, 'name' => $meal->category->name] : null,
            'is_favorited' => true,
            'favorited_at' => $favorite->created_at?->toIso8601String(),
        ];
    }
}
