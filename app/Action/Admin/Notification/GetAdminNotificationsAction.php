<?php

namespace App\Action\Admin\Notification;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

class GetAdminNotificationsAction
{
    public function execute(array $filters): array
    {
        $query = DatabaseNotification::query();

        // Filter by type
        if (!empty($filters['type'])) {
            $query->where('data->type', $filters['type']);
        }

        $query->latest();

        $perPage = min(max((int) ($filters['per_page'] ?? 15), 1), 100);
        $notifications = $query->paginate($perPage);

        return [
            'notifications' => $notifications,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
        ];
    }
}
