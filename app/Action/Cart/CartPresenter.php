<?php

namespace App\Action\Cart;

use App\Models\Cart;
use App\Services\ShippingService;

class CartPresenter
{
    public function present(Cart $cart, ?float $shippingFee = null, ?float $totalWithShipping = null): array
    {
        $cart->loadMissing(['items.meal.category', 'items.meal.subcategory']);

        $items = $cart->items->map(function ($item) {
            $meal = $item->meal;

            return [
                'id' => $item->id,
                'meal' => [
                    'id' => $meal->id,
                    'title' => $meal->title,
                    'slug' => $meal->slug,
                    'image_url' => $meal->image_url,
                    ...$meal->getApiPriceAttributes(),
                    'rating' => (float) $meal->rating,
                    'size' => $meal->size,
                    'brand' => $meal->brand,
                    'stock_quantity' => $meal->stock_quantity,
                    'is_available' => $meal->is_available,
                    'in_stock' => $meal->isInStock(),
                    'category' => $meal->category ? [
                        'id' => $meal->category->id,
                        'name' => $meal->category->name,
                    ] : null,
                    'subcategory' => $meal->subcategory ? [
                        'id' => $meal->subcategory->id,
                        'name' => $meal->subcategory->name,
                    ] : null,
                ],
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'subtotal' => (float) $item->subtotal,
            ];
        })->toArray();

        $data = [
            'id' => $cart->id,
            'status' => $cart->isEmpty() ? 'empty' : 'not empty',
            'items' => $items,
            'item_count' => $cart->item_count,
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'discount' => (float) $cart->discount,
            'total' => (float) $cart->total,
            'is_empty' => $cart->isEmpty(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];

        if ($shippingFee !== null && $totalWithShipping !== null) {
            $data['shipping_fee'] = (float) $shippingFee;
            $data['total_with_shipping'] = (float) $totalWithShipping;
        }

        return $data;
    }
}
