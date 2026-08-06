<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GetNotificationsAction
{
    public function run(User $user, Request $request): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) $request->get('per_page', 15)));

        $query = $user->notifications();

        if ($request->has('read')) {
            $isRead = filter_var($request->read, FILTER_VALIDATE_BOOLEAN);
            $query = $isRead ? $query->read() : $query->unread();
        }

        if ($request->has('type')) {
            $query->where('data->type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                    ->orWhere('data->body', 'like', "%{$search}%");
            });
        }

        $allowedOrderBy = ['created_at', 'read_at'];
        $orderBy = in_array((string) $request->get('order_by', 'created_at'), $allowedOrderBy, true)
            ? (string) $request->get('order_by', 'created_at')
            : 'created_at';
        $orderDirection = strtolower((string) $request->get('order_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($orderBy, $orderDirection)->paginate($perPage);
    }
}
