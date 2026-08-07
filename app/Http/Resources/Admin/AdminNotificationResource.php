<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->data) ? $this->data : json_decode($this->data, true) ?? [];

        return [
            'id' => $this->id,
            'type' => $data['type'] ?? 'unknown',
            'title' => $data['title'] ?? 'Notification',
            'body' => $data['body'] ?? '',
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? 'View',
            'priority' => $data['priority'] ?? 'normal',
            'is_read' => !is_null($this->read_at),
            'read_at' => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
