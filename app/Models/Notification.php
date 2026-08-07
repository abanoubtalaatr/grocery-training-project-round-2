<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification as BaseDatabaseNotification;

class Notification extends BaseDatabaseNotification
{
    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function getTitleAttribute(): ?string
    {
        return $this->data['title'] ?? null;
    }

    public function getBodyAttribute(): ?string
    {
        return $this->data['body'] ?? null;
    }

    public function getTypeAttribute(): ?string
    {
        return $this->data['type'] ?? $this->attributes['type'] ?? null;
    }

    public function markAsRead()
    {
        $this->update([
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'read_at' => null,
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('data->type', $type);
    }
}