<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'driver_id',
        'order_number',
        'payment_method',
        'coupon_code',
        'payment_method_id',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'delivery_type',
        'pickup_location',
        'pickup_location_confirmed',
        'status',
        'subtotal',
        'tax',
        'discount',
        'shipping_fee',
        'total',
        'notes',
        'driver_latitude',
        'driver_longitude',
        'driver_location_updated_at',
        'placed_at',
        'processing_at',
        'shipping_at',
        'out_for_delivery_at',
        'delivered_at',
        'cancelled_at',
        'estimated_delivery_time',
        'schedule_delivery',
        'delivery_speed',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'total' => 'decimal:2',
        'driver_latitude' => 'decimal:8',
        'driver_longitude' => 'decimal:8',
        'driver_location_updated_at' => 'datetime',
        'pickup_location_confirmed' => 'boolean',
        'placed_at' => 'datetime',
        'processing_at' => 'datetime',
        'shipping_at' => 'datetime',
        'out_for_delivery_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        } while (static::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get the user that owns the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the address for the order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the order items for the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status position (1, 2, 3, 4, 5).
     */
    public function getStatusPositionAttribute(): int
    {
        return match($this->status) {
            'awaiting_payment' => 0,
            'placed' => 1,
            'processing' => 2,
            'shipping' => 3,
            'out_for_delivery' => 4,
            'delivered' => 5,
            'cancelled' => 0,
            default => 0,
        };
    }

    /**
     * Get status description.
     */
    public function getStatusDescriptionAttribute(): string
    {
        return match($this->status) {
            'awaiting_payment' => 'Awaiting payment',
            'placed' => 'Order placed',
            'processing' => 'Processing',
            'shipping' => 'Shipping',
            'out_for_delivery' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    /**
     * Scope a query to only include placed orders.
     */
    public function scopePlaced($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope a query to only include pending orders (for backward compatibility).
     */
    public function scopePending($query)
    {
        return $query->where('status', 'placed');
    }

    /**
     * Scope a query to only include active orders (not cancelled or delivered).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'delivered']);
    }
    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class);
    }
    public function specialNote(): BelongsTo
    {
        return $this->belongsTo(SpecialNote::class);
    }

    public function getSpecialNoteAttribute(): ?string
    {
        // Check if the main order 'notes' field exists and return it if present.
        if (!empty($this->notes)) {
            return $this->notes;
        }

        // Use eager-loaded notes relationship if available, otherwise query
        $orderNotes = $this->relationLoaded('notes')
            ? $this->getRelation('notes')
            : $this->notes()->get();

        $orderNote = $orderNotes->first();
        if ($orderNote) {
            // Use eager-loaded specialNote if available, otherwise query
            $specialNote = $orderNote->relationLoaded('specialNote')
                ? $orderNote->getRelation('specialNote')
                : $orderNote->specialNote;

            if ($specialNote) {
                return $specialNote->name;
            }
            if (!empty($orderNote->notes)) {
                return $orderNote->notes;
            }
        }

        return null;
    }
}
