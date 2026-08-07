<?php

namespace App\Exceptions\Cart;

class CartQuantityLimitExceededException extends CartException
{
    public function __construct(int $currentQuantity, int $effectiveMax, int $maxPerProduct)
    {
        parent::__construct("Maximum {$maxPerProduct} units per product. You already have {$currentQuantity} in cart, maximum total is {$effectiveMax}");
    }
}