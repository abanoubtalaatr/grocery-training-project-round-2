<?php

namespace App\Actions\Api\Notification;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class GetNotificationsByTypeAction
{
    public function run(User $user, string $type): LengthAwarePaginator
    {
        return $user->notifications()
            ->where('data->type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
