<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartListList extends Model
{
    use HasFactory;

    protected $table = 'smart_lists';
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'description',
        'image',
        'notify_on_price_drop',
        'notify_on_offers',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
