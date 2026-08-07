<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationStatsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'total' => $this->resource['total'],
            'unread' => $this->resource['unread'],
            'read' => $this->resource['read'],
            'by_type' => $this->resource['by_type'],
            'recent_types' => $this->resource['recent_types'],
            'last_notification_at' => $this->resource['last_notification_at'],
        ];
    }
}