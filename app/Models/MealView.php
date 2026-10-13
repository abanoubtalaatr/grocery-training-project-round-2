<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealView extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'meal_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    /**
     * Get the user that viewed the meal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the meal that was viewed.
     */
    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }
}
