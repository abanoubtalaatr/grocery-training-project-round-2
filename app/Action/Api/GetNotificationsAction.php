<?php

namespace App\Action\Api;

class GetNotificationsAction
{
    public function execute($user, array $filters = []): array
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 15)));
        $query = $user->notifications();

        if (isset($filters['read'])) {
            $isRead = filter_var($filters['read'], FILTER_VALIDATE_BOOLEAN);
            $query = $isRead ? $query->whereNotNull('read_at') : $query->whereNull('read_at');
        }

        if (!empty($filters['type'])) {
            $query->where('data->type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                    ->orWhere('data->body', 'like', "%{$search}%");
            });
        }

        $allowedOrderBy = ['created_at', 'read_at'];
        $orderBy = in_array($filters['order_by'] ?? 'created_at', $allowedOrderBy) 
            ? $filters['order_by'] 
            : 'created_at';
        $orderDirection = strtolower($filters['order_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($orderBy, $orderDirection);

        $notifications = $query->paginate($perPage);

        return [
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
            'total_count' => $user->notifications()->count(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ];
    }
}