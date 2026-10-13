<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'coupon_code',
        'subtotal',
        'tax',
        'discount',
        'coupon_discount',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'coupon_discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate and update cart totals.
     */
    public function calculateTotals(): void
    {
        $this->load('items');
        
        $subtotal = (float) $this->items->sum('subtotal');
        $itemDiscount = (float) $this->items->sum('discount_amount');
        $couponDiscount = 0.0;

        if ($this->coupon_code) {
            $offer = Offer::where('code', $this->coupon_code)->first();
            if ($offer && $offer->isValid() && $offer->canApplyToAmount($subtotal)) {
                $couponDiscount = round((float) $offer->calculateDiscount($subtotal), 2);
            } else {
                $this->coupon_code = null;
                $couponDiscount = 0.0;
            }
        }

        $taxableSubtotal = max(0, $subtotal - $couponDiscount);
        $taxRate = (float) Setting::getSettings()->tax_rate;
        if ($taxRate <= 0) {
            $taxRate = 0.1; // Default 10% tax rate
        }
        $tax = round($taxableSubtotal * $taxRate, 2);
        $totalDiscount = $itemDiscount + $couponDiscount;
        $total = round($taxableSubtotal + $tax, 2);

        $this->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $totalDiscount,
            'coupon_code' => $this->coupon_code,
            'coupon_discount' => $couponDiscount,
            'total' => $total,
        ]);
    }

    /**
     * Get the number of items in the cart.
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Scope a query to only include active carts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if cart is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items()->count() === 0;
    }
}
