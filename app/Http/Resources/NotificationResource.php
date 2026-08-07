<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'type' => data_get($this->data, 'type'),

            'title' => data_get($this->data, 'title', 'Notification'),

            'body' => data_get($this->data, 'body'),

            'action_url' => data_get($this->data, 'action_url'),

            'action_label' => data_get($this->data, 'action_label', 'View'),

            'priority' => data_get($this->data, 'priority', 'normal'),

            'is_read' => ! is_null($this->read_at),

            'read_at' => $this->read_at,

            'created_at' => $this->created_at,

            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}