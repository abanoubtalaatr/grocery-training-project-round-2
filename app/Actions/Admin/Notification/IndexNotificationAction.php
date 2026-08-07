<?php

namespace App\Actions\Admin\Notification;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexNotificationAction
{
    public function run(Request $request): array
    {
        $query = Notification::with('notifiable')
            ->orderByDesc('created_at');

        if ($search = $request->input('search')) {
            $query->where('data->title', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->where('data->type', $type);
        }

        $notifications = $query
            ->paginate(25)
            ->withQueryString();

        $types = Notification::query()
            ->select(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data, '$.type')) as type")
            )
            ->whereNotNull('data')
            ->distinct()
            ->pluck('type')
            ->filter()
            ->sort()
            ->values();

        return compact('notifications', 'types');
    }
}