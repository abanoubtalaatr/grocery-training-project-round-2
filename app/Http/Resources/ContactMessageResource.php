<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'subject' => $this->subject,
            'message' => $this->message,
            'reply' => $this->reply,
            'replied_at' => $this->replied_at?->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'human_date' => $this->created_at->diffForHumans(),
        ];
    }
}